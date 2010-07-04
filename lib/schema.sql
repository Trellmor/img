CREATE TABLE images (
location VARCHAR(255),
path VARCHAR(255),
original_name VARCHAR(255),
ip INTEGER,
time INTEGER,
user VARCHAR(255),
md5 VARCHAR(32),
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
CREATE TABLE users (
user VARCHAR(255) UNIQUE PRIMARY KEY,
cookie VARCHAR(32),
last_login INTEGER
);

