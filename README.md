## This is a very early release, be warned.

# my-portfolio-personal-blog
A simple, to the point, but very useful portfolio + personal blog that intends to showcase projects you have worked on.

Written mostly in PHP, you can create and edit posts, search the blog, order paginated results, and update your details.

The front page is a automatically updated with portfolio items, take a look at the it running, for my own use at http://joeycumines.me/

## Licence

Please see the file 'UNLICENCE'.

## Usage

I will briefly outline how to set up this application on a LAMP stack.

### Requirements

- The user must have a LAMP or WAMP stack installed
- The user must use the same domain for the life of the application, or it will break internal links and images in-article.
- Library requirements
  - jQuery v1.12.2
  - Bootstrap v3.3.6
  - ckeditor + (markdown plugin optional)
  - Here is a zip to extract into the root: https://drive.google.com/file/d/0B1JTntMu0v0-b2R3RDYwaGFiZ2c/view?usp=sharing

### Steps
- Setup the LAMP stack
- import structure.sql into the mysql server, includes create schema
- in php/db_connection.php set the MySQL details
- in php/db_connection.php set the timezone you wish to use (or leave it as AEST) (you may need to change the AEST string hardcoded in posts)
- somewhere in the web directory create a folder r/w accessible to all with sticky bits (google) (just used for images) (danger on shared hosts)
- in php/db_connection.php set the resource folder for images
- in templates/menubar.php set the menu bar brand text
- in templates/footer.php set the footer text

- in php/db_connection.php set the admin hash and salt for login, this will be changed to a actual GUI changeable feature eventually
  - If you wish to change the login page code directly, you can compare to a plain text stored password

Try out your website.

## Limitations

As of writing, there are currently no tools to change password, or change any of the global settings without editing the scripts directly.
