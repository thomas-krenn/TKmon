CREATE TABLE user(
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  salt TEXT NOT NULL
);

INSERT INTO user VALUES(
  1,
  'root',
  '4dd38d871065d61a9b80cf21ebabd72d',
  'XnIPQBc7LwMI3MB5jeOVgxbn2fNxczzqTdXci4IC'
);

-- EOF