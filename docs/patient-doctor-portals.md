# Patient, doctor, and admin quick guide

For the full Logikaal healthcare guide, see [USER-GUIDE.md](USER-GUIDE.md).

## Patient

- Sign in at `/patient/login`, or register at `/portal/register`.
- Browse doctors at `/portal/doctors`, open a profile, choose an available slot, and
  confirm the appointment.
- See your bookings and public chamber updates at `/portal/patient`.
- See test prices without signing in at `/tests`.

## Doctor

- Sign in at `/doctor/login` using the account provided by the hospital administrator.
- View your own chamber queue at `/portal/doctor`.
- Update each visit status as appropriate.
- Publish a public chamber status for availability, a visit in progress, break, delay,
  or end of day. Enter the expected return in minutes or hours when taking a break or
  reporting a delay. Keep patient details out of the public note.

## Administrator

- Sign in at `/admin/login`.
- Open `/portal/admin` to manage doctor profiles and schedules and to view all doctors'
  appointments.
- Open `/portal/admin/tests` to maintain test prices and dated discounts.
- Admin accounts are managed through **Users and Roles** in the hospital dashboard or
  `/users`.

## Time and sample data

Appointment and chamber times use `Asia/Dhaka`. Sample records in the demo are not
verified hospital information. Replace them before real use. Online demo credentials are
kept in a private local file and are not published in this guide.
