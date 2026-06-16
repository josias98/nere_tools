from __future__ import annotations

import tempfile
from pathlib import Path

import streamlit as st

from timesheet_generator import (
    employees_from_dataframe,
    generate_many,
    load_key_table,
    parse_date,
    parse_holidays,
    zip_files,
)

APP_DIR = Path(__file__).resolve().parent
UI_LOGO = APP_DIR / "assets" / "logo.png"
DEFAULT_LOGO = APP_DIR / "assets" / "logo_extracted.jpg"
DEFAULT_SAMPLE = APP_DIR / "employees_template.csv"

st.set_page_config(page_title="Générateur de feuilles de temps Néré Capital", layout="wide")


def load_auth_credentials() -> tuple[str, str] | None:
    """Return configured credentials, or None when Streamlit secrets are missing."""
    try:
        auth_config = st.secrets.get("auth", {})
        username = str(auth_config.get("username", "")).strip()
        password = str(auth_config.get("password", ""))
    except Exception:
        return None

    if not username or not password:
        return None
    return username, password


def require_authentication() -> None:
    if st.session_state.get("authenticated"):
        return

    st.title("Générateur de feuilles de temps")
    st.caption("Connexion requise")

    credentials = load_auth_credentials()
    if credentials is None:
        st.error(
            "Les identifiants ne sont pas configurés. Ajoutez une section [auth] "
            "avec username et password dans les secrets Streamlit de l'application."
        )
        st.stop()

    expected_username, expected_password = credentials
    with st.form("login_form"):
        username = st.text_input("Nom d'utilisateur")
        password = st.text_input("Mot de passe", type="password")
        submitted = st.form_submit_button("Se connecter", type="primary")

    if submitted:
        if username.strip() == expected_username and password == expected_password:
            st.session_state["authenticated"] = True
            st.rerun()
        else:
            st.error("Nom d'utilisateur ou mot de passe incorrect.")

    st.stop()


def render_sidebar_header() -> None:
    if UI_LOGO.exists():
        st.sidebar.image(str(UI_LOGO), width=180)
    else:
        st.sidebar.markdown("### Néré Capital")

    if st.sidebar.button("Se déconnecter"):
        st.session_state["authenticated"] = False
        st.rerun()


require_authentication()
render_sidebar_header()

st.title("Générateur de feuilles de temps")
st.caption("Néré Capital · PDF mensuels générés automatiquement à partir d'une clé de répartition")

with st.expander("Mode d'emploi", expanded=False):
    st.markdown(
        """
1. Importer la clé de répartition au format CSV ou Excel.
2. Vérifier les colonnes et compléter, si nécessaire, le responsable hiérarchique.
3. Choisir la période.
4. Générer puis télécharger les PDFs.

Par défaut, la colonne IPAS est fixée à 0%. Si une colonne IPAS existe dans le fichier importé, elle sera utilisée.
Pour Germaine/DAF-AAF et Job/DG, la feuille ajoute une colonne Autre projets, calculée pour que CATAL1,5°T + IPDE + Autre projets = 100%.
La date de signature est le premier jour ouvré suivant la fin de chaque mois. Les jours ouvrés excluent les samedis, les dimanches et les jours fériés que vous renseignez.
"""
    )

uploaded = st.file_uploader("Importer la clé de répartition", type=["csv", "xlsx", "xls"])

if uploaded is not None:
    df = load_key_table(uploaded)
else:
    df = load_key_table(DEFAULT_SAMPLE)
    st.info("Aucun fichier importé : l'application utilise la clé d'exemple fournie dans le dossier.")

st.subheader("1. Clé de répartition")
edited = st.data_editor(df, num_rows="dynamic", use_container_width=True)

employees = employees_from_dataframe(edited, ipas_default="0%")

# Display the selector with Prénom + Nom instead of the surname alone.
# The selected values are the same labels, so the filtering remains simple and transparent.
all_employee_labels = [e.display_name for e in employees]
default_selection = all_employee_labels[:2] if len(all_employee_labels) >= 2 else all_employee_labels
selected_employee_labels = st.multiselect(
    "Salariés à générer",
    options=all_employee_labels,
    default=default_selection,
)

st.subheader("2. Période et paramètres")
col1, col2, col3 = st.columns(3)
with col1:
    start_date = st.date_input("Date de début", value=parse_date("2026-01-01"), format="DD/MM/YYYY")
with col2:
    end_date = st.date_input("Date de fin", value=parse_date("2026-06-30"), format="DD/MM/YYYY")
with col3:
    output_label = st.text_input("Libellé du fichier ZIP", value="Feuilles_de_temps_S1_2026")

holidays_text = st.text_area(
    "Jours fériés ou non ouvrés à exclure des dates de signature, un par ligne, format YYYY-MM-DD ou DD/MM/YYYY",
    value="",
    height=90,
)

logo_upload = st.file_uploader("Logo optionnel", type=["png", "jpg", "jpeg"], key="logo")

st.subheader("3. Génération")
if st.button("Générer les feuilles de temps PDF", type="primary"):
    selected = [e for e in employees if e.display_name in selected_employee_labels]
    if not selected:
        st.error("Aucun salarié sélectionné.")
    elif end_date < start_date:
        st.error("La date de fin doit être postérieure ou égale à la date de début.")
    else:
        try:
            holidays = parse_holidays(holidays_text)
            with tempfile.TemporaryDirectory() as tmpdir:
                tmp = Path(tmpdir)
                logo_path = None
                if logo_upload is not None:
                    logo_path = tmp / logo_upload.name
                    logo_path.write_bytes(logo_upload.getvalue())
                elif DEFAULT_LOGO.exists():
                    logo_path = DEFAULT_LOGO

                pdf_paths = generate_many(
                    selected,
                    start_date,
                    end_date,
                    tmp / "pdf",
                    logo_path=logo_path,
                    holidays=holidays,
                )
                zip_path = tmp / f"{output_label}.zip"
                zip_files(pdf_paths, zip_path)
                zip_bytes = zip_path.read_bytes()

            st.success(f"{len(selected)} feuille(s) générée(s) avec succès.")
            st.download_button(
                "Télécharger le ZIP",
                data=zip_bytes,
                file_name=f"{output_label}.zip",
                mime="application/zip",
            )
        except Exception as exc:
            st.exception(exc)
