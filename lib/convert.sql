BEGIN;
DROP INDEX idx_images_time;
DROP INDEX idx_images_md5;
DROP INDEX idx_imagetags_image;
DROP INDEX idx_tags_count;
DROP INDEX idx_tags_tag;
DROP INDEX idx_tags_text;

CREATE TEMPORARY TABLE temp_images AS SELECT ROWID,* FROM images;
DROP TABLE images;
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
INSERT INTO images SELECT * FROM temp_images;
DROP TABLE temp_images;

CREATE TEMPORARY TABLE temp_imagetags AS SELECT ROWID,* FROM imagetags;
DROP TABLE imagetags;
CREATE TABLE imagetags (
id INTEGER,
image INTEGER,
tag INTEGER,
PRIMARY KEY (id ASC)
);
INSERT INTO imagetags SELECT * FROM temp_imagetags;
DROP TABLE temp_imagetags;

CREATE TEMPORARY TABLE temp_tags AS SELECT ROWID,* FROM tags;
DROP TABLE tags;
CREATE TABLE tags (
id INTEGER,
tag VARCHAR(255),
text VARCHAR(255),
count INTEGER DEFAULT 0,
PRIMARY KEY (id ASC)
);
INSERT INTO tags SELECT * FROM temp_tags;
DROP TABLE temp_tags;

create index idx_images_time on images(time);
create index idx_images_md5 on images(md5);
create index idx_imagetags_image_tag on imagetags(image,tag);
create index idx_tags_count on tags(count);
create index idx_tags_tag on tags(tag);
create index idx_tags_text on tags(text);
COMMIT;
