SELECT 'CREATE DATABASE graduation_test'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'graduation_test')\gexec
