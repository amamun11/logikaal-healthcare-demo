# Logikaal healthcare user guide

This guide covers the features currently set up for Logikaal healthcare. The public
preview is at <https://logikaal-healthcare.infinityfreeapp.com>. The local development
site runs at <http://127.0.0.1:8001> when the Laravel server is running.

The preview contains sample records. Doctor qualifications, chamber details, test prices,
and patient information must be checked and replaced with hospital-approved data before
real use.

## Start here

The public website now has these pages:

- Home: `/`
- Specialities: `/specialties`
- Public doctor search: `/find-doctor`
- Appointment information: `/appointment-info`
- Tests & prices: `/tests`
- Address and directions: `/contact`
- Patient & visitor guide and FAQs: `/patient-guide`
- Health-check package examples: `/health-packages`

The listed address is Laxmipur, Rajpara, Rajshahi 6000, Bangladesh. The public doctor
cards are prototype records: online directories for other hospitals are not proof that
those doctors work at Logikaal healthcare. Confirm names, credentials, chamber details,
and appointment availability before a real public launch.

The sample test prices are market-reference figures from public Rajshahi provider lists,
not Logikaal healthcare's approved prices. Sources are linked on `/tests`; confirm each
test, method, branch, room, price, and offer with the hospital before publishing.

The site offers three separate sign-in pages:

- Patient: `/patient/login`
- Doctor: `/doctor/login`
- Admin: `/admin/login`

The online review accounts and their current passwords are stored in a private local
CSV file. Use that file for preview sign-in; do not put its passwords in this guide or
send them in a public link. For a locally seeded development database, use the local
credentials saved for the project. Local and online passwords are separate.

All appointment and chamber times are shown in Bangladesh time (`Asia/Dhaka`).

## Patient tasks

1. Sign in at `/patient/login`, or choose the patient registration link to create an
   account.
2. Choose **Find a doctor**. Search by doctor's name or department.
3. Open a doctor's profile to review the department, qualifications/details, chamber
   floor and room, working days, and opening hours.
4. Choose an available date and time, optionally enter a visit reason, and confirm.
5. Open **My appointments** to see the booking, serial, doctor, chamber location, and
   visit status. The patient portal shows only the signed-in patient's own bookings.
6. Public live chamber updates appear on doctor cards and appointment pages and refresh
   automatically.

The public **Tests & prices** page at `/tests` can be viewed without signing in. It shows
the listed test, price, location, current discount, and payable price when an offer is
active.

## Doctor tasks

1. Sign in at `/doctor/login` with the doctor account created by an administrator.
2. On **My chamber**, choose the chamber date and view the booked patients for that
   doctor. Doctors see their own queue, not other doctors' patients.
3. Update a visit's status when the patient arrives, completes the visit, cancels, or
   does not attend.
4. Before stepping away, open **Update live chamber status**, choose **On break** or
   **Delayed**, enter an expected return such as `45 minutes` or `1 hour`, and publish.
   For **Seeing a patient**, select the current appointment. Use a short public note if
   helpful; do not put private medical details in it.
5. Set the status back to **Available** when ready, or **Finished for today** when the
   chamber closes.

The live status shows the doctor's current status, last update time, serial being seen
when supplied, expected return for a break/delay, and public note. It does not publish
the patient's medical details.

## Administrator tasks

Sign in at `/admin/login`. The legacy hospital dashboard is at `/dashboard`.

### Doctors, profiles, logins, and schedules

1. Open **Doctor schedules & bookings** from the dashboard or visit `/portal/admin`.
2. Select **Add a doctor** to create a doctor record. Fill in the doctor's name,
   department, qualification/speciality, profile details, room, floor, working days,
   chamber start/end time, and minutes per visit.
3. Enable **Accept online bookings** only when the schedule is ready.
4. Open **Profile, hours & login** on an existing doctor's card to update those details.
   Existing appointments are not automatically cancelled when hours change.
5. To create a linked login for a doctor without one, enter a unique username and
   password in the **Doctor login** section. To reset an existing doctor's password,
   enter and confirm a new password. Leave the password boxes empty to keep it.
6. Save the form. Share any new or changed password privately with that doctor.

The administration page also shows all doctors' live status and chamber bookings. Filter
bookings by date or doctor. An appointment created for a patient is visible to the
administrator and to the assigned doctor.

### Test catalogue, prices, and offers

1. Open **Tests & prices** from the admin schedule page or visit
   `/portal/admin/tests`.
2. Add a test with its name, base price in taka, description, floor, room, and optional
   reference range; or edit an existing catalogue entry.
3. To publish a discount, enter the percentage and both the inclusive start and end
   dates. A same-day offer begins and ends on the selected Bangladesh date.
4. Save the test. Set the discount to `0` to clear the offer.

The public catalogue is `/tests`. Expired offers remain listed with their dates but are
not applied to the payable price. Changing catalogue prices does not rewrite old lab or
invoice records.

### Admin accounts and passwords

Use the dashboard's **Users and Roles** section or `/users` to find an account. Open its
edit form, change the appropriate fields, keep its role/group as **Admin**, and save the
update. The current legacy account form requires the password field on update; use a new
password when saving. Keep the new password private and record it in a password manager.

Passwords are stored as one-way hashes. If an account cannot be updated from the user
screen, ask the project maintainer to add a dedicated password reset flow instead of
editing a password directly in the database.

## Understanding the database

The local development app and hosted preview each use their own MySQL database. Changes
made in phpMyAdmin or MySQL Workbench affect only the database connection you selected;
they do not automatically copy between local and online environments.

Common records live in tables such as `users`, `patients`, `doctors`, `appointments`,
and the test catalogue table. The website forms are the preferred way to add or update
records: they check required fields, hash passwords, keep doctor accounts linked, and
protect appointment availability. Use phpMyAdmin to view table structure and rows, or
to take a backup. Avoid changing password hashes or deleting linked records directly.
Export a database backup before any manual database change.

## Known limits in this preview

- Doctor schedules repeat weekly and allow one daily chamber interval. Date-specific
  leave and multiple shifts in one day are not included yet.
- There is no password recovery or email/phone verification flow yet.
- Sample doctors, qualifications, patients, and test prices are for presentation and
  should be replaced with verified hospital data.
- The free preview host is for review. Confirm its current limits and make backups before
  relying on it for ongoing work.

## Local development commands

Run these from the Laravel project folder in PowerShell. `.env` must already point at
the local database and its password:

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

To apply a new project migration to the selected local database:

```powershell
php artisan migrate
```

Check the `.env` database name before running migration commands. Do not run local
database setup or seed commands against the hosted production database by mistake.
