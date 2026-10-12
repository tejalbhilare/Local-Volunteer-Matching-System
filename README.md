# Local Volunteer Matching System

A web-based application that connects volunteers with organizations offering community service opportunities. The system allows volunteers to explore opportunities and track their applications, while organizations can publish opportunities and manage applicants.

## Features

### Volunteer
- Register and log in securely.
- Browse available volunteering opportunities.
- Apply for opportunities.
- View application statuses.
- Manage volunteer profile information.

### Organization
- Access an organization dashboard.
- Create and publish volunteering opportunities.
- Manage existing opportunities.
- View applicants.
- Accept or reject applications.

### Admin
- Access the admin dashboard.
- View system statistics.
- Manage registered users.

## Technologies Used

- **Frontend:** HTML, CSS
- **Backend:** PHP
- **Database:** MySQL
- **Local Development Environment:** XAMPP
- **Version Control:** Git and GitHub

## Installation and Setup

1. Install [XAMPP](https://www.apachefriends.org/).
2. Clone or download this repository.
3. Place the project folder inside the XAMPP `htdocs` directory.
4. Start Apache and MySQL.
5. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
6. Create a MySQL database named `local_volunteer_db`.
7. Import the project's database SQL file, if available. If no SQL file exists, create the required tables using the project's database structure.
8. Check `config/database.php` and update the database credentials if necessary.
9. Open the application in your browser:

   `http://localhost/Local-Volunteer-Matching-System/`

## Project Structure

```text
Local-Volunteer-Matching-System/
├── admin/
│   ├── dashboard.php
│   └── users.php
├── assets/
│   └── style.css
├── config/
│   └── database.php
├── organization/
│   ├── applicants.php
│   ├── create-opportunity.php
│   ├── dashboard.php
│   └── manage-opportunities.php
├── volunteer/
│   ├── applications.php
│   ├── apply.php
│   ├── dashboard.php
│   └── profile.php
├── index.php
├── login.php
├── logout.php
├── opportunities.php
├── register.php
└── README.md
```

*Note: The structure above lists the main files; your local project may contain additional files.*

## Application Workflow

1. A user registers and logs in.
2. Volunteers browse available opportunities and submit applications.
3. Organizations publish opportunities and review applications.
4. Organizations accept or reject volunteer applications.
5. Volunteers check their application statuses.

## Running the Project

Run the application locally using XAMPP. Apache serves the PHP pages, and MySQL stores application data.

## Future Enhancements

- Email notifications for application status changes.
- Search and filter opportunities by location or skills.
- Improved form validation and security.
- Volunteer hours tracking and reporting.
- Responsive interface improvements.

## Author

**Tejal Arun Bhilare**

B.Sc. Information Technology Student

GitHub: [tejalbhilare](https://github.com/tejalbhilare)

## License

This project was developed for educational purposes. A formal open-source license can be added if the project is distributed for reuse.