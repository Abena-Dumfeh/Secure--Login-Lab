
# Secure Login System – Authentication Foundations

A web-based login system built with PHP + MySQL to practice secure authentication basics.

## What’s implemented (Stage 1)
- User signup and login flow
- Password hashing (bcrypt) stored in MySQL
- Incorrect credentials denied
- Session-based authentication + protected dashboard
- Logout session destruction

## What I verified so far
- Passwords are not stored in plaintext
- Wrong password cannot access the dashboard
- Only authenticated users can access protected pages

## Next (Stage 2: Security testing)
- SQL injection testing
- Brute-force / rate-limit testing
- Session security checks
- CSRF testing & protection

## Tech stack
PHP • MySQL • Apache • HTML/CSS • Linux

> Note: This is a learning/security lab project built in stages.
