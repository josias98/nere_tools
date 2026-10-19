# Signature cryptographique des PDF

## Périmètre

Nouvel outil « Signature PDF », réservé aux comptes actifs autorisés dans le portail.
Chaque collaborateur importe et signe ses propres documents. Les autres comptes,
y compris les administrateurs, ne peuvent pas lire ses fichiers par les routes de
l’outil. Le PDF original reste conservé dans le stockage privé.

Le collaborateur donne son accord dans sa session authentifiée. Le certificat
configuré sur le serveur applique ensuite un **sceau d’entreprise** au PDF. Le nom
du collaborateur est une déclaration signée dans les métadonnées et le journal ;
il n’est pas l’identité personnelle certifiée par l’autorité émettrice.

## Moteur

`signerphp/pdf-signer` 0.2.0 (MIT) et `signerphp/pdf-core` 0.1.1 ajoutent une
révision incrémentale au PDF, sans reconstruire les pages. Le profil demandé est
PAdES Baseline B, RSA/SHA-256, avec signature CMS détachée `ETSI.CAdES.detached`.
L’extension PHP OpenSSL vérifie ensuite le contenu signé et l’empreinte du
certificat. Aucun exécutable OpenSSL, Java, Python ou worker n’est nécessaire sur
le serveur. La conformité PAdES complète n’a pas fait l’objet d’une certification.

Le premier moteur évalué, SAPP, a été écarté après le refus d’un PDF ReportLab
standard comportant un commentaire dans le trailer. SignerPHP est une dépendance
récente : versions verrouillées dans Composer, essais sur le corpus documentaire
réel nécessaires avant activation en production. Ne pas modifier `vendor/`.

Cette première version accepte des PDF simples de 10 Mo et 100 pages maximum.
Elle refuse les PDF déjà signés, chiffrés, avec formulaires ou contenu actif.
Elle ne supprime ni ne convertit ces éléments à l’insu du collaborateur.
Les signatures apparaissent dans le panneau de signatures du lecteur PDF ; aucun
dessin de paraphe n’est ajouté sur les pages.

## Certificat de développement

Configurer localement, dans `.env` :

```dotenv
PDF_SIGNATURE_MODE=test
PDF_SIGNATURE_PASSWORD="choisir-un-secret-local-d-au-moins-12-caracteres"
PDF_SIGNATURE_CERTIFICATE_PATH="/chemin/absolu/Tools/storage/app/private/signature-keys/development.p12"
```

Puis exécuter :

```console
php artisan config:clear
php artisan signatures:make-test-certificate
```

La commande crée un certificat RSA 3072 bits auto-signé de 365 jours, clairement
nommé `NERE Tools - DEVELOPPEMENT`, et refuse d’écraser un fichier existant.
Elle n’est disponible qu’en environnement `local` ou `testing`. Le mode `test`
est également refusé par le service de signature dans les autres environnements.
Le certificat et son mot de passe ne doivent jamais être committés.

## Activation sur le serveur

1. Installer les dépendances verrouillées, compiler les assets, livrer la release
   et appliquer les migrations suivant la procédure de déploiement du projet.
2. Obtenir un certificat de signature de documents RSA (2048 bits minimum),
   émis par une autorité, avec sa clé privée au format PKCS#12 `.p12` ou `.pfx`.
   Inclure les certificats intermédiaires lors de l’export PKCS#12.
3. Placer ce fichier **hors de `public/`**, avec lecture limitée au compte PHP
   (0600 sur Linux). Garder le secret dans l’environnement du serveur.
4. Définir `PDF_SIGNATURE_MODE=production`, `PDF_SIGNATURE_CERTIFICATE_PATH`
   (chemin absolu) et `PDF_SIGNATURE_PASSWORD`, puis recharger la configuration.
5. Signer un document d’essai et contrôler dans un lecteur indépendant le contenu,
   la chaîne de confiance et le certificat avant d’ouvrir l’accès aux utilisateurs.

Par défaut, le mode est `disabled`. Aucun certificat de test n’est créé ou utilisé
automatiquement. Les certificats expirés, pas encore valides, les clés non RSA,
les clés trop courtes et les certificats auto-signés en mode production sont
refusés. La clé privée n’est jamais envoyée au navigateur ou inscrite dans les
preuves, les logs ou la base.

## Portée des vérifications

La validation interne contrôle la signature cryptographique, la couverture complète
du fichier, l’absence d’octets ajoutés après la signature, le certificat attendu
et l’empreinte conservée en base. Elle ne prétend pas valider des PDF externes.
La confiance publique dépend de la chaîne et du magasin de certificats du lecteur.
Les contrôles internes ne consultent pas les listes de révocation OCSP/CRL.

L’heure enregistrée est celle du serveur. Aucun horodatage tiers RFC 3161 ni
matériel de validation longue durée n’est inclus dans cette version. Elle ne
revendique pas une signature personnelle qualifiée ni une équivalence juridique
automatique avec une signature manuscrite. Le statut juridique dépend notamment
du certificat et des règles applicables à l’usage du document.

La clé d’entreprise étant détenue par le serveur, son exploitation doit être
réservée aux personnes habilitées et sa sauvegarde protégée. Une compromission
du serveur peut compromettre cette clé et les journaux applicatifs.

## Contrôles de développement

`PdfSignatureEngineTest` produit de vraies signatures et vérifie leur validité avec
OpenSSL, puis vérifie le rejet des modifications du contenu, des octets ajoutés
et d’un autre certificat. La preuve initiale a aussi été vérifiée par l’exécutable
OpenSSL externe avec `cms -verify -binary -inform DER -content ... -noverify`.
`-noverify` dispense uniquement de la confiance dans le certificat auto-signé ;
la signature et le digest sont bien contrôlés.

Le parcours est couvert par `PdfSigningWorkflowTest` : consentement, contrôle des
accès, certificat et document inchangés depuis la relecture, double soumission,
échec d’enregistrement, intégrité au téléchargement et export de preuve JSON.
L’aperçu utilise PDF.js 6.4.299 en rendu canvas, sans exécuter de script PDF.

Au 19 octobre 2026, les audits Composer/npm ne signalent aucun avis pour ces
nouvelles dépendances. L’audit global signale toutefois 24 avis sur 7 dépendances
PHP existantes et 5 paquets JavaScript existants ; leur mise à jour reste à traiter.

Références techniques : [SignerPHP](https://github.com/SignerPHP/pdf-signer),
[PDF.js](https://github.com/mozilla/pdf.js),
[OpenSSL CMS dans PHP](https://www.php.net/manual/en/function.openssl-cms-verify.php).
