CREATE TABLE images (
location VARCHAR(255),
original_name VARCHAR(255),
ip INTEGER,
time INTEGER
);
CREATE TABLE imagetags (
image INTEGER,
tag INTEGER
);
CREATE TABLE tags (
tag VARCHAR(255),
text VARCHAR(255),
count INTEGER DEFAULT 0
);

