from __future__ import annotations

import argparse
import calendar
import io
import math
import re
import zipfile
from dataclasses import dataclass, asdict
from datetime import date, datetime, timedelta
from pathlib import Path
from typing import Iterable, List, Optional, Sequence, Tuple

import pandas as pd
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.platypus import Image, PageBreak, Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle

MONTH_NAMES_FR = {
    1: "Janvier",
    2: "Février",
    3: "Mars",
    4: "Avril",
    5: "Mai",
    6: "Juin",
    7: "Juillet",
    8: "Août",
    9: "Septembre",
    10: "Octobre",
    11: "Novembre",
    12: "Décembre",
}

DEFAULT_CODE_ANALYTIQUE = "1.1.1 Personnel technique"
DEFAULT_LOCATION = "Ouagadougou"
DEFAULT_ENTITY = "NERE CAPITAL PARTNERS"


@dataclass
class Employee:
    prenom: str
    nom: str
    entite: str = DEFAULT_ENTITY
    pays: str = "Burkina Faso"
    fonction: str = ""
    role: str = ""
    fonds: str = ""
    autre_projets: str = ""
    ipas: str = "0%"
    catal: str = "0%"
    ipde: str = "0%"
    code_analytique: str = DEFAULT_CODE_ANALYTIQUE
    lieu: str = DEFAULT_LOCATION
    nom_signature: str = ""
    responsable_hierarchique: str = ""
    signature_droite_titre: str = ""

    @property
    def display_name(self) -> str:
        """Employee name as displayed in the app and in the timesheet header: Prénom + Nom."""
        return f"{self.prenom} {self.nom}".strip()

    @property
    def full_name(self) -> str:
        # Backward-compatible alias used for filenames and CLI filters.
        return self.display_name

    @property
    def signature_name(self) -> str:
        return self.nom_signature.strip() or self.display_name

    @property
    def is_dg(self) -> bool:
        haystack = f"{self.role} {self.fonction}".lower()
        return "dg" in haystack or "directeur général" in haystack or "directeur general" in haystack

    @property
    def right_signature_title(self) -> str:
        if self.signature_droite_titre.strip():
            return self.signature_droite_titre.strip()
        if self.is_dg:
            return "DAF"
        return "responsable hiérarchique"

    @property
    def uses_autre_projets(self) -> bool:
        """Return True for the DAF/AAF and DG profiles that need an 'Autre projets' allocation column."""
        haystack = f"{self.display_name} {self.role} {self.fonction}".lower()
        return any(
            token in haystack
            for token in [
                "daf",
                "aaf",
                "dg",
                "directeur général",
                "directeur general",
                "financi",
            ]
        )

    @property
    def autre_projets_percent(self) -> str:
        """Allocation that balances CATAL1.5°T and IPDE to 100% for DAF/DG profiles."""
        # A manually provided 'Autre projets' column takes precedence. Otherwise the
        # value is calculated so that CATAL + IPDE + Autre projets = 100%.
        if not _is_empty(self.autre_projets):
            return format_percent(self.autre_projets, default="0%")
        catal = percent_to_number(self.catal)
        ipde = percent_to_number(self.ipde)
        return format_percent_number(100 - catal - ipde)


# The source key has heterogeneous spellings. This dictionary keeps the app permissive.
COLUMN_ALIASES = {
    "prenom": "prenom",
    "prénom": "prenom",
    "first_name": "prenom",
    "nom": "nom",
    "last_name": "nom",
    "entite": "entite",
    "entité": "entite",
    "pays": "pays",
    "fonction": "fonction",
    "poste": "fonction",
    "role": "role",
    "rôle": "role",
    "ipas": "ipas",
    "fonds": "fonds",
    "autre projet": "autre_projets",
    "autre projets": "autre_projets",
    "autres projets": "autre_projets",
    "autre_projet": "autre_projets",
    "autre_projets": "autre_projets",
    "autres_projets": "autre_projets",
    "ipde": "ipde",
    "catal": "catal",
    "catal1.5": "catal",
    "catal1,5": "catal",
    "catal1.5°": "catal",
    "catal1,5°": "catal",
    "catal1.5°t": "catal",
    "catal1,5°t": "catal",
    "catal1.5°t ": "catal",
    "catal1,5°t ": "catal",
    "code analytique": "code_analytique",
    "code_analytique": "code_analytique",
    "lieu": "lieu",
    "localisation": "lieu",
    "nom signature": "nom_signature",
    "nom_signature": "nom_signature",
    "signature": "nom_signature",
    "responsable": "responsable_hierarchique",
    "responsable hierarchique": "responsable_hierarchique",
    "responsable hiérarchique": "responsable_hierarchique",
    "manager": "responsable_hierarchique",
    "titre signature droite": "signature_droite_titre",
    "libelle signature droite": "signature_droite_titre",
    "libellé signature droite": "signature_droite_titre",
    "titre cosignataire": "signature_droite_titre",
    "titre co-signataire": "signature_droite_titre",
    "qualite cosignataire": "signature_droite_titre",
    "qualité cosignataire": "signature_droite_titre",
    "signature droite titre": "signature_droite_titre",
}


def _normalise_column_name(name: object) -> str:
    raw = str(name).strip().lower()
    raw = raw.replace("\n", " ")
    raw = re.sub(r"\s+", " ", raw)
    return COLUMN_ALIASES.get(raw, raw)


def _is_empty(value: object) -> bool:
    if value is None:
        return True
    try:
        if isinstance(value, float) and math.isnan(value):
            return True
    except TypeError:
        pass
    return str(value).strip() in {"", "-", "nan", "None"}


def format_percent(value: object, default: str = "0%") -> str:
    """Return a human-readable percent while preserving explicit strings when possible."""
    if _is_empty(value):
        return default

    if isinstance(value, str):
        txt = value.strip()
        if txt == "-":
            return default
        # keep French decimal comma when the source already uses it
        if "%" in txt:
            return txt.replace(" ", "")
        txt2 = txt.replace(",", ".")
        try:
            num = float(txt2)
        except ValueError:
            return txt
    else:
        num = float(value)

    # Excel may provide 0.96 for 96%, or 96 for 96%.
    if 0 <= num <= 1:
        num *= 100
    if num.is_integer():
        return f"{int(num)}%"
    return f"{num:.2f}%".replace(".", ",")


def percent_to_number(value: object, default: float = 0.0) -> float:
    """Convert a percent-like value to a number expressed on a 0-100 scale."""
    if _is_empty(value):
        return default
    if isinstance(value, str):
        txt = value.strip().replace("%", "").replace(" ", "").replace(",", ".")
        if not txt or txt == "-":
            return default
        try:
            num = float(txt)
        except ValueError:
            return default
    else:
        try:
            num = float(value)
        except Exception:
            return default
    if 0 <= num <= 1:
        num *= 100
    return num


def format_percent_number(num: float) -> str:
    """Format a numeric 0-100 percent value for display in the PDF."""
    # Avoid cosmetic artefacts such as -0% after floating point operations.
    if abs(num) < 1e-9:
        num = 0.0
    rounded = round(num, 2)
    if float(rounded).is_integer():
        return f"{int(rounded)}%"
    return f"{rounded:.2f}%".replace(".", ",")


def load_key_table(path_or_buffer) -> pd.DataFrame:
    """Load CSV/XLSX key table and normalise column names."""
    if hasattr(path_or_buffer, "name"):
        name = path_or_buffer.name.lower()
    else:
        name = str(path_or_buffer).lower()

    if name.endswith(".xlsx") or name.endswith(".xls"):
        df = pd.read_excel(path_or_buffer)
    else:
        # sep=None allows comma or semicolon CSV. Useful in Francophone Excel exports.
        df = pd.read_csv(path_or_buffer, sep=None, engine="python")

    df = df.dropna(how="all").copy()
    df.columns = [_normalise_column_name(c) for c in df.columns]

    # Drop fully highlighted/template rows where the employee name is absent.
    if "nom" in df.columns:
        df = df[~df["nom"].apply(_is_empty)]
    return df.reset_index(drop=True)


def employees_from_dataframe(df: pd.DataFrame, ipas_default: str = "0%") -> List[Employee]:
    employees: List[Employee] = []
    for _, row in df.iterrows():
        prenom = "" if _is_empty(row.get("prenom")) else str(row.get("prenom")).strip()
        nom = "" if _is_empty(row.get("nom")) else str(row.get("nom")).strip()
        if not prenom and not nom:
            continue

        # In the Nere key, the source column is "Fonds". The timesheet model, however,
        # has an IPAS column. Per the user's instruction, IPAS defaults to 0 unless an
        # explicit IPAS column is added in the imported key.
        explicit_ipas = row.get("ipas") if "ipas" in df.columns else None
        ipas = format_percent(explicit_ipas, default=ipas_default)

        employee = Employee(
            prenom=prenom,
            nom=nom,
            entite=str(row.get("entite", DEFAULT_ENTITY)).strip() if not _is_empty(row.get("entite")) else DEFAULT_ENTITY,
            pays=str(row.get("pays", "Burkina Faso")).strip() if not _is_empty(row.get("pays")) else "Burkina Faso",
            fonction=str(row.get("fonction", "")).strip() if not _is_empty(row.get("fonction")) else "",
            role=str(row.get("role", "")).strip() if not _is_empty(row.get("role")) else "",
            fonds=format_percent(row.get("fonds"), default="") if "fonds" in df.columns else "",
            autre_projets=format_percent(row.get("autre_projets"), default="") if "autre_projets" in df.columns else "",
            ipas=ipas,
            catal=format_percent(row.get("catal"), default="0%"),
            ipde=format_percent(row.get("ipde"), default="0%"),
            code_analytique=str(row.get("code_analytique", DEFAULT_CODE_ANALYTIQUE)).strip()
            if not _is_empty(row.get("code_analytique"))
            else DEFAULT_CODE_ANALYTIQUE,
            lieu=str(row.get("lieu", DEFAULT_LOCATION)).strip() if not _is_empty(row.get("lieu")) else DEFAULT_LOCATION,
            nom_signature=str(row.get("nom_signature", "")).strip() if not _is_empty(row.get("nom_signature")) else "",
            responsable_hierarchique=str(row.get("responsable_hierarchique", "")).strip()
            if not _is_empty(row.get("responsable_hierarchique"))
            else "",
            signature_droite_titre=str(row.get("signature_droite_titre", "")).strip()
            if not _is_empty(row.get("signature_droite_titre"))
            else "",
        )
        employees.append(employee)

    apply_special_cosignature_rules(employees)
    return employees


def apply_special_cosignature_rules(employees: List[Employee]) -> None:
    """Apply Néré-specific co-signature conventions.

    Particular case: the DG / DG Fonds timesheet is co-signed by the DAF,
    not by a generic line manager. If the imported key does not explicitly
    provide that cosigner, the app tries to identify Germaine/AAF/DAF in the
    same key and uses her signature name.
    """
    daf_candidates = [
        e for e in employees
        if any(token in f"{e.role} {e.fonction}".lower() for token in ["daf", "aaf", "financi"])
    ]
    germaine_candidates = [e for e in employees if "germaine" in e.display_name.lower()]
    daf = germaine_candidates[0] if germaine_candidates else (daf_candidates[0] if daf_candidates else None)

    dg_candidates = [e for e in employees if e.is_dg]
    job_candidates = [e for e in dg_candidates if "job" in e.display_name.lower()]
    dg = job_candidates[0] if job_candidates else (dg_candidates[0] if dg_candidates else None)

    for employee in employees:
        if employee.is_dg:
            if not employee.signature_droite_titre.strip():
                employee.signature_droite_titre = "DAF"
            if not employee.responsable_hierarchique.strip() and daf is not None:
                employee.responsable_hierarchique = daf.signature_name
        elif employee.uses_autre_projets:
            # DAF/AAF profiles are generally cosigned by the DG when no explicit
            # manager is provided in the imported key.
            if not employee.responsable_hierarchique.strip() and dg is not None:
                employee.responsable_hierarchique = dg.signature_name


def signature_block_title(employee: Employee) -> str:
    title = employee.right_signature_title.strip()
    title_lower = title.lower()
    if title_lower in {"responsable", "responsable hierarchique", "responsable hiérarchique", "manager"}:
        return "Signature du responsable hiérarchique :"
    if title.upper() == "DAF":
        return "Signature du DAF :"
    if title_lower.startswith("signature"):
        return title if title.endswith(":") else f"{title} :"
    return f"Signature du {html_escape(title)} :"


def parse_date(value: object) -> date:
    if isinstance(value, date) and not isinstance(value, datetime):
        return value
    if isinstance(value, datetime):
        return value.date()
    value = str(value).strip()
    for fmt in ("%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y"):
        try:
            return datetime.strptime(value, fmt).date()
        except ValueError:
            continue
    raise ValueError(f"Date invalide: {value}. Formats acceptés: YYYY-MM-DD, DD/MM/YYYY.")


def month_boundaries_between(start: date, end: date) -> List[Tuple[int, int, date, date]]:
    """Return (year, month, period_start, period_end) for every covered month."""
    if end < start:
        raise ValueError("La date de fin doit être postérieure ou égale à la date de début.")

    result = []
    y, m = start.year, start.month
    while (y, m) <= (end.year, end.month):
        first = date(y, m, 1)
        last = date(y, m, calendar.monthrange(y, m)[1])
        period_start = max(start, first)
        period_end = min(end, last)
        result.append((y, m, period_start, period_end))
        if m == 12:
            y, m = y + 1, 1
        else:
            m += 1
    return result


def month_weeks(period_start: date, period_end: date) -> List[Tuple[date, date]]:
    """Split a period into first partial week, then Monday-Sunday blocks, then final partial week."""
    weeks = []
    cur = period_start
    while cur <= period_end:
        days_to_sunday = 6 - cur.weekday()
        wk_end = min(cur + timedelta(days=days_to_sunday), period_end)
        weeks.append((cur, wk_end))
        cur = wk_end + timedelta(days=1)
    return weeks


def first_working_day_after(end_date: date, holidays: Optional[Iterable[date]] = None) -> date:
    """First business day after end_date. Business day = Monday-Friday and not in holidays."""
    holidays_set = set(holidays or [])
    current = end_date + timedelta(days=1)
    while current.weekday() >= 5 or current in holidays_set:
        current += timedelta(days=1)
    return current


def fmt_date(d: date) -> str:
    return d.strftime("%d/%m/%Y")


def html_escape(text: object) -> str:
    return (
        str(text)
        .replace("&", "&amp;")
        .replace("<", "&lt;")
        .replace(">", "&gt;")
        .replace("\n", "<br/>")
    )


def p(text: object, style: ParagraphStyle, bold_label: Optional[str] = None, allow_markup: bool = False) -> Paragraph:
    content = str(text) if allow_markup else html_escape(text)
    if bold_label is None:
        return Paragraph(content, style)
    return Paragraph(f"<b>{html_escape(bold_label)}</b>{content}", style)


def build_styles():
    base = getSampleStyleSheet()
    normal = ParagraphStyle(
        "normal_compact",
        parent=base["Normal"],
        fontName="Helvetica",
        fontSize=9.2,
        leading=10.6,
        alignment=TA_LEFT,
        spaceAfter=0,
        spaceBefore=0,
    )
    small = ParagraphStyle(
        "small",
        parent=normal,
        fontSize=8.7,
        leading=10,
    )
    header = ParagraphStyle(
        "table_header",
        parent=normal,
        fontName="Helvetica-Bold",
        alignment=TA_CENTER,
        fontSize=9.1,
        leading=10.4,
    )
    title = ParagraphStyle(
        "title",
        parent=base["Title"],
        fontName="Helvetica-Bold",
        fontSize=16,
        leading=18,
        alignment=TA_CENTER,
        spaceAfter=12,
    )
    centered = ParagraphStyle(
        "centered",
        parent=normal,
        alignment=TA_CENTER,
    )
    signature = ParagraphStyle(
        "signature",
        parent=normal,
        fontSize=9.5,
        leading=12,
    )
    return {"normal": normal, "small": small, "header": header, "title": title, "centered": centered, "signature": signature}


def month_table(employee: Employee, year: int, month: int, period_start: date, period_end: date, holidays: Optional[Iterable[date]] = None) -> Table:
    styles = build_styles()
    weeks = month_weeks(period_start, period_end)
    sig_date = first_working_day_after(period_end, holidays)

    if employee.uses_autre_projets:
        allocation_headers = [
            p("CATAL1,5°T", styles["header"]),
            p("IPDE", styles["header"]),
            p("Autre projets", styles["header"]),
        ]
        allocation_values = [employee.catal, employee.ipde, employee.autre_projets_percent]
        allocation_col_widths = [28 * mm, 25 * mm, 35 * mm]
        comment_width = A4[0] - 20 * mm - (18 * mm + 29 * mm + 29 * mm + sum(allocation_col_widths))
        col_widths = [18 * mm, 29 * mm, 29 * mm, *allocation_col_widths, comment_width]
    else:
        allocation_headers = [
            p("IPAS", styles["header"]),
            p("CATAL1,5°T", styles["header"]),
            p("IPDE", styles["header"]),
        ]
        allocation_values = [employee.ipas, employee.catal, employee.ipde]
        page_width = A4[0] - 20 * mm
        col_widths = [18 * mm, 29 * mm, 29 * mm, 25 * mm, 30 * mm, 25 * mm, page_width - 156 * mm]

    header_cells = [
        [p(employee.entite, styles["normal"], "Entité :  "), "", "", "", "", "", ""],
        [p(employee.display_name, styles["normal"], "Nom et prénom du salarié :  "), "", "", "", "", "", ""],
        [p(employee.lieu, styles["normal"], "Lieu :  "), "", "", "", "", "", ""],
        [p(employee.fonction, styles["normal"], "Intitulé du poste :  "), "", "", "", "", "", ""],
        [p(employee.code_analytique, styles["normal"], "Code analytique:  "), "", "", "", "", "", ""],
        ["", "", "", "", "", "", ""],
        [p(MONTH_NAMES_FR[month], styles["normal"], "Mois :  "), "", "", p(str(year), styles["normal"], "Année :  "), "", "", ""],
        [
            p("Semaine", styles["header"]),
            p("Date de début", styles["header"]),
            p("Date de fin", styles["header"]),
            *allocation_headers,
            p("Commentaires /<br/>Détails", styles["header"], allow_markup=True),
        ],
    ]

    week_rows = []
    for idx, (start, end) in enumerate(weeks, start=1):
        week_rows.append(
            [
                p(str(idx), styles["centered"]),
                p(fmt_date(start), styles["centered"]),
                p(fmt_date(end), styles["centered"]),
                *[p(value, styles["centered"]) for value in allocation_values],
                p("", styles["normal"]),
            ]
        )

    avg_row = [
        p("<b>Moyenne</b>", styles["normal"], allow_markup=True),
        "",
        "",
        *[p(f"<b>{html_escape(value)}</b>", styles["centered"], allow_markup=True) for value in allocation_values],
        "",
    ]
    signature_left = Paragraph(
        f"<b>Signature du salarié :</b><br/><br/><i>Date : {fmt_date(sig_date)}</i><br/><i>Nom et Prénom : {html_escape(employee.signature_name)}</i>",
        styles["signature"],
    )
    signature_right = Paragraph(
        f"<b>{signature_block_title(employee)}</b><br/><br/><i>Date : {fmt_date(sig_date)}</i><br/><i>Nom et Prénom : {html_escape(employee.responsable_hierarchique)}</i>",
        styles["signature"],
    )
    signature_row = [signature_left, "", "", "", signature_right, "", ""]

    data = header_cells + week_rows + [avg_row, signature_row]

    row_heights = [6 * mm, 6 * mm, 6 * mm, 6 * mm, 6 * mm, 8 * mm, 18 * mm, 10 * mm]
    row_heights += [6 * mm for _ in week_rows]
    row_heights += [6 * mm, 46 * mm]

    table = Table(data, colWidths=col_widths, rowHeights=row_heights, repeatRows=0)

    avg_row_idx = 8 + len(week_rows)
    sig_row_idx = avg_row_idx + 1

    table_style = TableStyle(
        [
            ("GRID", (0, 0), (-1, -1), 0.45, colors.HexColor("#999999")),
            ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
            ("BACKGROUND", (0, 5), (-1, 5), colors.HexColor("#C9C9C9")),
            ("SPAN", (0, 0), (-1, 0)),
            ("SPAN", (0, 1), (-1, 1)),
            ("SPAN", (0, 2), (-1, 2)),
            ("SPAN", (0, 3), (-1, 3)),
            ("SPAN", (0, 4), (-1, 4)),
            ("SPAN", (0, 5), (-1, 5)),
            ("SPAN", (0, 6), (2, 6)),
            ("SPAN", (3, 6), (-1, 6)),
            ("SPAN", (0, avg_row_idx), (2, avg_row_idx)),
            ("SPAN", (0, sig_row_idx), (3, sig_row_idx)),
            ("SPAN", (4, sig_row_idx), (-1, sig_row_idx)),
            ("VALIGN", (0, sig_row_idx), (-1, sig_row_idx), "TOP"),
            ("LEFTPADDING", (0, 0), (-1, -1), 2.5),
            ("RIGHTPADDING", (0, 0), (-1, -1), 2.5),
            ("TOPPADDING", (0, 0), (-1, -1), 1.8),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 1.8),
        ]
    )
    table.setStyle(table_style)
    return table

def build_pdf_for_employee(
    employee: Employee,
    start_date: date,
    end_date: date,
    output_path: Path,
    logo_path: Optional[Path] = None,
    holidays: Optional[Iterable[date]] = None,
) -> Path:
    output_path = Path(output_path)
    output_path.parent.mkdir(parents=True, exist_ok=True)
    doc = SimpleDocTemplate(
        str(output_path),
        pagesize=A4,
        leftMargin=10 * mm,
        rightMargin=10 * mm,
        topMargin=9 * mm,
        bottomMargin=8 * mm,
    )
    styles = build_styles()
    story = []
    periods = month_boundaries_between(start_date, end_date)

    for idx, (year, month, period_start, period_end) in enumerate(periods):
        if logo_path and Path(logo_path).exists():
            try:
                img = Image(str(logo_path), width=38 * mm, height=22 * mm)
                img.hAlign = "LEFT"
                story.append(img)
            except Exception:
                story.append(Spacer(1, 22 * mm))
        else:
            story.append(Spacer(1, 22 * mm))
        story.append(Paragraph("FEUILLE DE TEMPS", styles["title"]))
        story.append(month_table(employee, year, month, period_start, period_end, holidays=holidays))
        if idx != len(periods) - 1:
            story.append(PageBreak())

    doc.build(story)
    return output_path


def safe_filename(text: str) -> str:
    text = re.sub(r"[^A-Za-z0-9_\-]+", "_", text.strip())
    text = re.sub(r"_+", "_", text).strip("_")
    return text or "employe"


def generate_many(
    employees: Sequence[Employee],
    start_date: date,
    end_date: date,
    output_dir: Path,
    logo_path: Optional[Path] = None,
    holidays: Optional[Iterable[date]] = None,
) -> List[Path]:
    output_dir = Path(output_dir)
    output_dir.mkdir(parents=True, exist_ok=True)
    paths = []
    for employee in employees:
        period_label = f"{start_date.strftime('%Y%m%d')}_{end_date.strftime('%Y%m%d')}"
        filename = f"Feuilles_de_temps_{period_label}_{safe_filename(employee.full_name)}.pdf"
        path = build_pdf_for_employee(employee, start_date, end_date, output_dir / filename, logo_path=logo_path, holidays=holidays)
        paths.append(path)
    return paths


def zip_files(paths: Sequence[Path], zip_path: Path) -> Path:
    zip_path = Path(zip_path)
    zip_path.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(zip_path, "w", zipfile.ZIP_DEFLATED) as zf:
        for path in paths:
            zf.write(path, arcname=Path(path).name)
    return zip_path


def parse_holidays(values: Optional[Sequence[str] | str]) -> List[date]:
    if not values:
        return []
    if isinstance(values, str):
        chunks = re.split(r"[,;\n]+", values)
    else:
        chunks = list(values)
    dates = []
    for chunk in chunks:
        chunk = str(chunk).strip()
        if not chunk:
            continue
        dates.append(parse_date(chunk))
    return dates


def _cli() -> None:
    parser = argparse.ArgumentParser(description="Generate Nere Capital timesheets as PDFs.")
    parser.add_argument("--input", required=True, help="CSV or XLSX key table.")
    parser.add_argument("--start", required=True, help="Start date, e.g. 2026-01-01.")
    parser.add_argument("--end", required=True, help="End date, e.g. 2026-06-30.")
    parser.add_argument("--output-dir", default="output", help="Output directory.")
    parser.add_argument("--logo", default=None, help="Optional logo image path.")
    parser.add_argument("--employees", nargs="*", default=None, help="Optional employee full names to include.")
    parser.add_argument("--holidays", nargs="*", default=None, help="Optional non-working holidays, YYYY-MM-DD.")
    args = parser.parse_args()

    df = load_key_table(args.input)
    employees = employees_from_dataframe(df)
    if args.employees:
        wanted = {name.strip().lower() for name in args.employees}
        employees = [e for e in employees if e.full_name.lower() in wanted]
    if not employees:
        raise SystemExit("Aucun salarié trouvé.")

    paths = generate_many(
        employees,
        parse_date(args.start),
        parse_date(args.end),
        Path(args.output_dir),
        logo_path=Path(args.logo) if args.logo else None,
        holidays=parse_holidays(args.holidays),
    )
    zip_path = zip_files(paths, Path(args.output_dir) / "feuilles_de_temps.zip")
    print("Generated:")
    for path in paths:
        print(f"- {path}")
    print(f"ZIP: {zip_path}")


if __name__ == "__main__":
    _cli()
