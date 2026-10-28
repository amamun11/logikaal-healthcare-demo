# Logikaal healthcare

Hospital appointment and information portal for **Logikaal healthcare**. This project
includes separate patient, doctor, and administrator areas, online chamber booking,
doctor availability updates, and a public diagnostic test price list.

The public site includes a hospital landing page, specialities, doctor search, appointment
information, a patient and visitor guide, example health-check packages, a test catalogue,
and location/directions for Laxmipur, Rajpara, Rajshahi 6000. Its original Logikaal
healthcare mark is at
`public/assets/logikaal-healthcare-mark.svg`.

## Project links

- Public preview: <https://logikaal-healthcare.infinityfreeapp.com>
- Full user guide: [docs/USER-GUIDE.md](docs/USER-GUIDE.md)
- Patient, doctor, and admin guide: [docs/patient-doctor-portals.md](docs/patient-doctor-portals.md)

## Local development

Open a terminal in the project folder, install PHP dependencies if needed, configure
the local `.env` database settings, then run:

```powershell
php artisan key:generate
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8001
```

Open <http://127.0.0.1:8001>. Do not copy production database credentials into a
shared file or commit `.env`.

## Main features

- Patient, doctor, and administrator sign-in.
- Doctor directory with department, qualifications, chamber location, and schedule.
- Patient appointment booking, serial numbers, and personal appointment list.
- Doctor queue and public live chamber status (available, consulting, break, delayed,
  or finished).
- Administrator view of doctors and appointments across the hospital.
- Public diagnostic test prices, room/floor information, and date-limited discounts.
- Patient and visitor guide with appointment preparation, check-in steps, FAQs, and clear
  notes for hospital policies that still need confirmation.
- Health-check package examples at `/health-packages`; example contents and prices are
  explicitly marked as unapproved pending hospital and clinician review.
- Bangladesh time (`Asia/Dhaka`) throughout appointment and chamber workflows.

Public Rajshahi provider prices linked from the test catalogue are comparison samples, not
Logikaal healthcare's official rates. The doctor profiles in the prototype also need
hospital confirmation; they do not claim affiliation based on online directory records.

## Preview records and credentials

Sample information is for review only. The hosted preview's reviewer usernames and
passwords are kept in a private local file; they are intentionally not published here.
Replace sample names, schedules, credentials, and test prices with hospital-approved
information before real use. Never use sample accounts for patient care.

The upstream `LICENSE` is preserved. `NOTICE-LOGIKAAL.txt` describes the Logikaal
healthcare customization.
