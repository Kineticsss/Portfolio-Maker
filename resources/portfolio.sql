-- CREATE TABLE users (
--     id SERIAL PRIMARY KEY,
--     first_name VARCHAR(100) NOT NULL,
--     last_name VARCHAR(100) NOT NULL,
--     email VARCHAR(150) UNIQUE NOT NULL,
--     password VARCHAR(255) NOT NULL, -- hashed
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- CREATE TABLE experience (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     title VARCHAR(150) NOT NULL,
--     company VARCHAR(150) NOT NULL,
--     start_date DATE,
--     end_date DATE,
--     description TEXT
-- );

-- CREATE TABLE projects (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     title VARCHAR(150) NOT NULL,
--     description TEXT,
--     link VARCHAR(255),                  -- GitHub/demo link
--     tech_stack VARCHAR(255),            -- optional (PHP, JS, etc.)
--     start_date DATE,
--     end_date DATE
-- );

-- CREATE TABLE education (
--     id SERIAL PRIMARY KEY,
--     user_id INT REFERENCES users(id) ON DELETE CASCADE,
--     school VARCHAR(150) NOT NULL,
--     degree VARCHAR(150) NOT NULL,
--     field VARCHAR(100),                 -- optional
--     start_date DATE,
--     end_date DATE,
--     description TEXT
-- );

-- CREATE TABLE skills (
--   id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
--   user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
--   skill_name VARCHAR(100) NOT NULL,
--   proficiency VARCHAR(50),
--   created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
-- );

-- ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255);