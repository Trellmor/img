CREATE TABLE users (
	id INTEGER PRIMARY KEY,
	user TEXT UNIQUE NOT NULL,
	mail TEXT,
	admin INTEGER NOT NULL
);
CREATE TABLE images (
	id INTEGER PRIMARY KEY,
	location TEXT NOT NULL,
	path TEXT NOT NULL,
	original_name TEXT NOT NULL,
	ip TEXT NOT NULL,
	time INTEGER NOT NULL,
	user INTEGER REFERENCES users(id),
	md5 VARCHAR(32) NOT NULL,
	uploadid VARCHAR(36),
	width INT NOT NULL,
	height INT NOT NULL,
	size INT NOT NULL
);
CREATE TABLE tags (
	id INTEGER PRIMARY KEY,
	tag TEXT UNIQUE NOT NULL COLLATE NOCASE,
	count INTEGER NOT NULL
);
CREATE TABLE imagetags (
	id INTEGER PRIMARY KEY,
	image INTEGER NOT NULL REFERENCES images(id),
	tag INTEGER NOT NULL REFERENCES tags(id)
);
CREATE UNIQUE INDEX idx_imagetags_image_tag ON imagetags(image,tag);
