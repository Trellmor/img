CREATE TABLE images (
id INTEGER,
location VARCHAR(255),
path VARCHAR(255),
original_name VARCHAR(255),
ip INTEGER,
time INTEGER,
user VARCHAR(255),
md5 VARCHAR(32),
PRIMARY KEY (id ASC)
);
CREATE TABLE imagetags (
id INTEGER,
image INTEGER,
tag INTEGER,
PRIMARY KEY (id ASC)
);
CREATE TABLE tags (
id INTEGER,
tag VARCHAR(255),
text VARCHAR(255),
count INTEGER DEFAULT 0,
PRIMARY KEY (id ASC)
);
CREATE TABLE users (
user VARCHAR(255) UNIQUE PRIMARY KEY,
cookie VARCHAR(32),
last_login INTEGER
);
create index idx_images_time on images(time);
create index idx_images_md5 on images(md5);
create index idx_imagetags_image_tag on imagetags(image,tag);
create index idx_tags_count on tags(count);
create index idx_tags_tag on tags(tag);
create index idx_tags_text on tags(text);

