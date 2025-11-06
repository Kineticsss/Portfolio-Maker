-- CREATE TABLE users (
--         id SERIAL PRIMARY KEY,
--         first_name VARCHAR(100),
--         last_name VARCHAR(100),
--         email VARCHAR(255) UNIQUE,
--         password VARCHAR(255),
--         about_me TEXT,
--         phone VARCHAR(20),
--         address VARCHAR(255),
--         github VARCHAR(255),
--         linkedin VARCHAR(255),
--         profile_picture VARCHAR(255),
--         public_token VARCHAR(64)
-- );

-- CREATE TABLE experience (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     job_title VARCHAR(150) NOT NULL,
--     company_name VARCHAR(150) NOT NULL,
--     location VARCHAR(150),
--     start_date DATE,
--     end_date DATE,
--     description TEXT
-- );


-- CREATE TABLE projects (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     project_name VARCHAR(150) NOT NULL,
--     description TEXT,
--     technologies VARCHAR(255),
--     project_link VARCHAR(255),
--     image_path VARCHAR(255)
-- );


-- CREATE TABLE education (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     school_name VARCHAR(150) NOT NULL,
--     degree VARCHAR(150) NOT NULL,
--     field_of_study VARCHAR(150),
--     start_date DATE,
--     end_date DATE,
--     description TEXT
-- );

-- CREATE TABLE skills (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     skill_name VARCHAR(100) NOT NULL,
--     category VARCHAR(100),
--     proficiency VARCHAR(20) CHECK (proficiency IN ('Beginner', 'Intermediate', 'Advanced', 'Expert'))
-- );

-- CREATE TABLE certifications (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     title VARCHAR(150) NOT NULL,
--     issuer VARCHAR(150) NOT NULL,
--     date_issued DATE,
--     description TEXT
-- );

-- CREATE TABLE languages (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     language_name VARCHAR(50) NOT NULL,
--     proficiency VARCHAR(20) CHECK (proficiency IN ('Basic', 'Conversational', 'Fluent', 'Native'))
-- );



-- ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255);

-- TRUNCATE TABLE users RESTART IDENTITY CASCADE; (To Reset All Tables)
-- ALTER TABLE users ADD COLUMN summary TEXT;

-- ALTER TABLE users
-- ADD COLUMN phone VARCHAR(20),
-- ADD COLUMN address TEXT,
-- ADD COLUMN github VARCHAR(255),
-- ADD COLUMN linkedin VARCHAR(255);

-- ALTER TABLE users ADD COLUMN public_token VARCHAR(64);

-- CREATE TABLE languages (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     language_name VARCHAR(50) NOT NULL,
--     proficiency VARCHAR(20) CHECK (proficiency IN ('Basic', 'Conversational', 'Fluent', 'Native'))
-- );

-- CREATE TABLE certifications (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     title VARCHAR(150) NOT NULL,
--     issuer VARCHAR(150) NOT NULL,
--     date_issued DATE,
--     description TEXT
-- );
