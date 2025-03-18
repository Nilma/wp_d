# Setting Up WordPress with Docker and Fetching Posts in a React App

## Prerequisites
- Docker installed on your system
- Docker Compose installed
- Node.js and npm installed for the React app
- A GitHub repository where you download and open the React app from Visual Studio Code

## Step 1: Create the `docker-compose.yml` File

Create a `docker-compose.yml` file with the following content:

```yaml
version: '3.8'

services:
  db:
    image: "mariadb:latest"
    platform: linux/arm64 # Use x86_64 for Windows
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: MyR00tMySQLPa$$5w0rD
      MYSQL_DATABASE: MyWordPressDatabaseName
      MYSQL_USER: MyWordpressUser
      MYSQL_PASSWORD: Pa$$5w0rD
    volumes:
      - db_data:/var/lib/mysql
  
  wordpress:
    depends_on:
      - db
    image: "wordpress:latest"
    platform: linux/arm64 # Use x86_64 for Windows
    restart: always
    ports:
      - "8000:80"
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_USER: MyWordpressUser
      WORDPRESS_DB_PASSWORD: Pa$$5w0rD
      WORDPRESS_DB_NAME: MyWordPressDatabaseName
    volumes:
      - ./wp-content/:/var/www/html/wp-content

  phpmyadmin:
    depends_on:
      - db
    image: "phpmyadmin:latest"
    platform: linux/arm64/v8 # Use x86_64 for Windows
    restart: always
    ports:
      - "8080:80"
    environment:
      PMA_HOST: db
      MYSQL_ROOT_PASSWORD: MyR00tMySQLPa$$5w0rD
      PMA_PASSWORD: Pa$$5w0rD

volumes:
  db_data:
  wp_data:
```

## Step 2: Start the WordPress and Database Containers
Run the following command to start the services:

```sh
docker-compose up -d
```

This will start:
- MariaDB (database)
- WordPress (running on `http://localhost:8000`)
- phpMyAdmin (running on `http://localhost:8080`)

## Step 3: Install the Custom WordPress Plugin

1. Inside the `wp-content/plugins/` folder, create a new folder named `footer-text-plugin`.
2. Inside `footer-text-plugin`, create a file named `footer-text-plugin.php` and add the following content:

```php
<?php
/*
Plugin Name: Footer Text Plugin
Author: Nilma Abbas
Author URI: www.example.com
Description: Adds text at bottom of posts.
Version: 1.0
*/
?>
<?php
// Function to add custom text to post content
function add_footer_text($content) {
    return $content . '<p>Custom footer text by Nilma Abbas.</p>';
}
// Hook function to 'the_content'
add_filter('the_content', 'add_footer_text');
?>
```

3. Go to `http://localhost:8000/wp-admin/` and activate the plugin in the WordPress admin panel.

## Step 4: Create a React App in Docker

### 1. Create a Dockerfile
Inside your project folder, create a file named `Dockerfile` and add the following content:

```dockerfile
# Use Node.js as the base image
FROM node:20-alpine

# Set the working directory inside the container
WORKDIR /app

# Copy package.json and package-lock.json first (for better caching)
COPY package.json package-lock.json ./

# Install dependencies
RUN npm install

# Copy the rest of the project files
COPY . .

# Expose port 3000 for the React app
EXPOSE 3000

# Start the React development server
CMD ["npm", "start"]
```

### 2. Create a `.dockerignore` File
To keep the image clean and prevent unnecessary files from being copied, create a `.dockerignore` file inside your project folder and add:

```
node_modules
build
.dockerignore
.git
.gitignore
```

### 3. Build and Run the Docker Container
#### Step 1: Build the Docker Image
Run the following command inside your project folder:

```sh
docker build -t my-react-app .
```

#### Step 2: Run the React App in Docker
```sh
docker run -p 3000:3000 -v $(pwd):/app -w /app my-react-app
```

Now, open `http://localhost:3000` in your browser.

### 4. Why Use This Approach?
- **Easier to manage** → No need to type long commands every time.
- **Consistent setup** → Anyone can build and run the app with Docker.
- **Modular approach** → Easily extendable for production and CI/CD.

### 5. Troubleshooting

If port 3000 is already in use, run:
```sh
docker ps  # Find running containers
```
Then stop the container using:
```sh
docker stop <container_id>
```
Alternatively, run the app on a different port:
```sh
docker run -p 3001:3000 my-react-app
```

## Step 5: Fetch WordPress Posts in React

Replace the `App.jsx` file content with the following:

```jsx
import { useEffect, useState } from 'react';
import './App.css';

function App() {
  const [posts, setPosts] = useState([]);

  useEffect(() => {
    fetch('http://localhost:8000/wp-json/wp/v2/posts?_embed')
      .then((res) => {
        if (!res.ok) {
          throw new Error('Network response was not ok');
        }
        return res.json();
      })
      .then((data) => setPosts(data))
      .catch((error) => console.error('Fetch error:', error));
  }, []);

  const postsJsx = posts.map((post) => (
    <li key={post.id} dangerouslySetInnerHTML={{ __html: post.content.rendered }}></li>
  ));

  return <ul>{postsJsx}</ul>;
}

export default App;
```

## Step 6: Start the React App in Docker
Navigate to the project folder and start the app:

```sh
npm install
npm run dev
```

Your React app will now fetch posts from the WordPress API (`http://localhost:8000/wp-json/wp/v2/posts`) and display them.

## Step 7: Configure WordPress Permalinks and Verify the Integration
1. Go to `http://localhost:8000/wp-admin/` and navigate to **Settings > Permalinks**.
2. Change the permalink structure to **Post name** and save the changes.
3. Create a new post in WordPress (`http://localhost:8000/wp-admin/`).
2. Check if the post appears in your React app (`http://localhost:5173` or the provided Vite port).

## Conclusion
You have successfully:
- Set up WordPress and MariaDB using Docker Compose.
- Created a WordPress plugin to add a custom footer text.
- Developed a React app inside Docker.
- Verified the integration.

You can now expand the React app by adding styling, pagination, or other WordPress API endpoints!

