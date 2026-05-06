
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(10) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT*FROM users;
ALTER TABLE users
ADD COLUMN dob DATE,
ADD COLUMN pan VARCHAR(10);

ALTER TABLE users
DROP COLUMN full_name;

ALTER TABLE users
ADD COLUMN first_name VARCHAR(50) NOT NULL,
ADD COLUMN last_name VARCHAR(50) NOT NULL;

CREATE TABLE user_tokens (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(255) NOT NULL,
    expiry_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SELECT*FROM user_tokens;

CREATE TABLE company (
    id SERIAL PRIMARY KEY,
    company_name VARCHAR(50) NOT NULL,
    industry VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

UPDATE company SET updated_at = current_timestamp WHERE id = 4;

UPDATE company set industry = 'Finance' WHERE id = 8;

UPDATE company set industry = 'IT Services' WHERE id = 4;

ALTER TABLE company
ADD COLUMN created_by INT REFERENCES users(id) ON DELETE SET NULL;
SELECT * FROM company;
SELECT*FROM company ORDER BY id DESC;
CREATE TABLE department (
    id SERIAL PRIMARY KEY,
    department_name VARCHAR(50) NOT NULL,
    company_id INT NOT NULL REFERENCES company (id) ON DELETE RESTRICT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT * FROM department;

CREATE TABLE employee (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(10) NOT NULL,
    company_id INT NOT NULL REFERENCES company (id) ON DELETE RESTRICT,
    department_id INT NOT NULL REFERENCES department (id) ON DELETE CASCADE,
    role VARCHAR(50) NOT NULL,
    join_date DATE,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT * FROM employee;

CREATE TABLE salary (
    id SERIAL PRIMARY KEY,
    employee_id INT NOT NULL REFERENCES employee (id) ON DELETE CASCADE,
    salary_month DATE NOT NULL,
    payment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SELECT*FROM salary;
-- upadted code start
CREATE TABLE salary_status (
    id SERIAL PRIMARY KEY,
    status_name VARCHAR(50) UNIQUE NOT NULL
);
SELECT*FROM salary_status;

INSERT INTO salary_status (status_name)
VALUES 
('Pending'),
('Paid'),
('Rejected');

ALTER TABLE salary 
ADD COLUMN gross NUMERIC(10,2),
ADD column deduction NUMERIC(10,2),
ADD column net NUMERIC(10,2),
ADD COLUMN salary_year SMALLINT,
ADD COLUMN salary_month_emp SMALLINT CHECK (salary_month_emp BETWEEN 1 AND 12),
ADD COLUMN status_id INT REFERENCES salary_status(id);


CREATE TABLE salary_component (
    id SERIAL PRIMARY KEY,
    component_name VARCHAR(50) UNIQUE NOT NULL,
);
SELECT*FROM salary_component;
-- ALTER TABLE salary_component ADD COLUMN component_type VARCHAR(20) NOT NULL;
ALTER TABLE salary_component ADD COLUMN component_type SMALLINT DEFAULT 1; COMMENT '1: Earning, 2: Deduction';

-- UPDATE salary_component SET component_type = 'earning'
-- WHERE component_name IN ('Basic', 'HRA', 'Bonus');

-- UPDATE salary_component SET component_type = 2
-- WHERE component_name IN ('PF', 'Deduction');



CREATE TABLE salary_details (
    id SERIAL PRIMARY key,
    salary_id INT NOT NULL REFERENCES salary (id) ON DELETE CASCADE,
    component_id INT NOT NULL REFERENCES salary_component (id),
    amount NUMERIC(10, 2) NOT null CHECK (amount >= 0)
);

SELECT * FROM salary_details;

-- updating the salary table data with newly added columns
UPDATE salary s
SET 
    gross = sub.gross,
    deduction = sub.deduction,
    net = sub.net,
    salary_year = EXTRACT(YEAR FROM s.salary_month),
    salary_month_emp = EXTRACT(MONTH FROM s.salary_month),
    status_id = 2
FROM (
    SELECT 
        s.id,
        SUM(CASE WHEN sc.component_type = 1 THEN sd.amount ELSE 0 END) AS gross,
        SUM(CASE WHEN sc.component_type = 2 THEN sd.amount ELSE 0 END) AS deduction,
        SUM(CASE 
            WHEN sc.component_type = 1 THEN sd.amount 
            ELSE -sd.amount 
        END) AS net
    FROM salary s
    JOIN salary_details sd ON sd.salary_id = s.id
    JOIN salary_component sc ON sc.id = sd.component_id
    GROUP BY s.id
) sub
WHERE s.id = sub.id;

--inserting the data
-- company data details
INSERT INTO
    company (company_name, industry)
VALUES ('TCS', 'IT Services'),
    ('Infosys', 'IT Services'),
    ('Wipro', 'IT Services'),
    (
        'HCL Technologies',
        'Telecom & IT'
    ),
    ('Tech Mahindra', 'Telecom'),
    ('Accenture', 'Consulting'),
    ('Capgemini', 'Consulting'),
    (
        'ICICI Bank',
        'Banking & Finance'
    );

SELECT * FROM company;
-- department data details
INSERT INTO
    department (department_name, company_id)
VALUES ('IT', 1),
    ('HR', 1),
    ('Finance', 1),
    ('IT', 2),
    ('HR', 2),
    ('IT', 3),
    ('Support', 3),
    ('IT', 4),
    ('Finance', 4),
    ('HR', 5),
    ('Consulting', 6),
    ('Consulting', 7),
    ('Banking', 8);

SELECT * FROM department;

--employee data details
INSERT INTO
    employee (
        first_name,
        last_name,
        email,
        phone,
        company_id,
        department_id,
        role,
        join_date
    )
VALUES (
        'Ravi',
        'Kumar',
        'ravi@gmail.com',
        '9120000001',
        1,
        1,
        'Developer',
        '2023-01-10'
    ),
    (
        'Kiran',
        'Reddy',
        'kiran@gmail.com',
        '9230000002',
        1,
        2,
        'HR Manager',
        '2022-05-15'
    ),
    (
        'Anjali',
        'Sharma',
        'anjali@gmail.com',
        '9340000003',
        2,
        4,
        'Developer',
        '2021-07-20'
    ),
    (
        'Vikram',
        'Singh',
        'vikram@gmail.com',
        '9450000004',
        3,
        6,
        'Support',
        '2024-02-01'
    ),
    (
        'Neha',
        'Verma',
        'neha@gmail.com',
        '9560000005',
        4,
        8,
        'Accountant',
        '2023-03-12'
    ),
    (
        'Rahul',
        'Das',
        'rahul@gmail.com',
        '9670000006',
        5,
        10,
        'HR',
        '2022-08-25'
    ),
    (
        'Pooja',
        'Nair',
        'pooja@gmail.com',
        '9780000007',
        6,
        11,
        'Consultant',
        '2023-09-10'
    ),
    (
        'Amit',
        'Joshi',
        'amit@gmail.com',
        '9890000008',
        7,
        12,
        'Consultant',
        '2024-01-05'
    ),
    (
        'Meena',
        'Kumari',
        'meena@gmail.com',
        '9240000011',
        8,
        13,
        'Bank Officer',
        '2025-02-10'
    ),
    (
        'Suresh',
        'Yadav',
        'suresh@gmail.com',
        '9350000012',
        3,
        7,
        'Support',
        '2024-12-20'
    ),
    (
        'Deepika',
        'Rao',
        'deepika@gmail.com',
        '9460000013',
        4,
        8,
        'Developer',
        '2025-03-15'
    ),
    (
        'Manoj',
        'Gupta',
        'manoj@gmail.com',
        '9570000014',
        5,
        10,
        'HR',
        '2023-06-18'
    ),
    (
        'Kavya',
        'Shetty',
        'kavya@gmail.com',
        '9680000015',
        6,
        11,
        'Consultant',
        '2022-10-10'
    ),
    (
        'Ramesh',
        'Naidu',
        'ramesh@gmail.com',
        '9790000016',
        7,
        12,
        'Consultant',
        '2024-04-01'
    ),
    (
        'Lakshmi',
        'Devi',
        'lakshmi@gmail.com',
        '9820000017',
        8,
        13,
        'Bank Officer',
        '2023-08-08'
    ),
    (
        'Pradeep',
        'Kumar',
        'pradeep@gmail.com',
        '9930000018',
        1,
        3,
        'Finance Analyst',
        '2022-09-09'
    ),
    (
        'Divya',
        'Menon',
        'divya@gmail.com',
        '9220000019',
        2,
        4,
        'Developer',
        '2023-12-12'
    ),
    (
        'Nikhil',
        'Shah',
        'nikhil@gmail.com',
        '9330000020',
        3,
        7,
        'Support',
        '2025-01-25'
    ),
    (
        'Harsha',
        'Vardhan',
        'harsha@gmail.com',
        '9440000021',
        4,
        8,
        'Developer',
        '2025-02-20'
    ),
    (
        'Teja',
        'Reddy',
        'teja@gmail.com',
        '9550000022',
        5,
        10,
        'HR',
        '2024-06-10'
    ),
    (
        'Bhavana',
        'Reddy',
        'bhavana@gmail.com',
        '9660000023',
        6,
        11,
        'Consultant',
        '2023-07-07'
    ),
    (
        'Ajay',
        'Kumar',
        'ajay@gmail.com',
        '9770000024',
        7,
        12,
        'Consultant',
        '2022-11-11'
    );

SELECT * FROM employee;
--salary components data details
INSERT INTO
    salary_component (component_name)
VALUES ('Basic'),
    ('HRA'),
    ('Bonus'),
    ('Deduction'),
    ('PF');

SELECT * FROM salary_component;

INSERT INTO
    salary (
        employee_id,
        salary_month,
        payment_date
    )
VALUES (1, '2026-01-01', '2026-01-30'),
    (1, '2026-02-01', '2026-02-27'),
    (2, '2026-01-01', '2026-01-31'),
    (3, '2026-01-01', '2026-01-29'),
    (4, '2026-01-01', '2026-01-30'),
    (5, '2026-01-01', '2026-01-31'),
    (6, '2026-01-01', '2026-01-28'),
    (7, '2026-01-01', '2026-01-30'),
    (8, '2026-01-01', '2026-01-31'),
    (9, '2026-01-01', '2026-01-29'),
    (
        10,
        '2026-02-01',
        '2026-02-28'
    );

SELECT * FROM salary;

INSERT INTO
    salary_details (
        salary_id,
        component_id,
        amount
    )
VALUES (1, 1, 50000),
    (1, 2, 12000),
    (1, 3, 6000),
    (1, 5, 2000),
    (1, 4, 2500),
    (2, 1, 52000),
    (2, 2, 12000),
    (2, 3, 4000),
    (2, 5, 2000),
    (2, 4, 2000),
    (3, 1, 45000),
    (3, 2, 8000),
    (3, 3, 2000),
    (3, 5, 1500),
    (3, 4, 1000),
    (4, 1, 40000),
    (4, 2, 9000),
    (4, 3, 3000),
    (4, 5, 1500),
    (4, 4, 1500),
    (5, 1, 42000),
    (5, 2, 8500),
    (5, 3, 2500),
    (5, 5, 1500),
    (5, 4, 1200),
    (6, 1, 38000),
    (6, 2, 7000),
    (6, 3, 2000),
    (6, 5, 1200),
    (6, 4, 1000),
    (7, 1, 47000),
    (7, 2, 9500),
    (7, 3, 3000),
    (7, 5, 1800),
    (7, 4, 1800),
    (8, 1, 46000),
    (8, 2, 9000),
    (8, 3, 2500),
    (8, 5, 1700),
    (8, 4, 1500),
    (9, 1, 48000),
    (9, 2, 10000),
    (9, 3, 3500),
    (9, 5, 1800),
    (9, 4, 2000),
    (10, 1, 44000),
    (10, 2, 8500),
    (10, 3, 2000),
    (10, 5, 1500),
    (10, 4, 1200),
    (11, 1, 55000),
    (11, 2, 12000),
    (11, 3, 5000),
    (11, 5, 2000),
    (11, 4, 2500);

SELECT * FROM salary_details;

