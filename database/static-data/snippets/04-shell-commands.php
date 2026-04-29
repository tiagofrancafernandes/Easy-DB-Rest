<?php

declare(strict_types=1);

return [
    // =========================
    // PostgreSQL - CLI Commands
    // =========================
    [
        'name' => 'pg-cli-connect.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Connect to local PostgreSQL
psql -U postgres -d database_name

# Connect to remote PostgreSQL
psql -h hostname -U username -d database_name

# Connect and execute command
psql -h hostname -U username -d database_name -c "SELECT * FROM users LIMIT 5;"

# Execute SQL file
psql -U postgres -d database_name -f script.sql

# List all databases
psql -U postgres -l

# List tables in database
psql -U postgres -d database_name -dt

# Backup database
pg_dump -U postgres -d database_name > backup.sql

# Restore database
psql -U postgres -d database_name < backup.sql
SHELL,
        'public_content_slug' => 'pg-cli-connect',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-cli-operations.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Connect to MySQL
mysql -u root -p -h localhost

# Connect with password in command
mysql -u root -pPASSWORD -h localhost database_name

# Execute SQL command
mysql -u root -p database_name -e "SELECT * FROM users LIMIT 5;"

# Execute SQL file
mysql -u root -p database_name < script.sql

# Backup database
mysqldump -u root -p database_name > backup.sql

# Restore database
mysql -u root -p database_name < backup.sql

# List all databases
mysql -u root -p -e "SHOW DATABASES;"

# List tables
mysql -u root -p -e "USE database_name; SHOW TABLES;"
SHELL,
        'public_content_slug' => 'mysql-cli-operations',
        'public_content_index' => false,
    ],

    // =========================
    // Git - Common Operations
    // =========================
    [
        'name' => 'git-workflow-commands.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Clone repository
git clone https://github.com/user/repo.git

# Initialize repository
git init

# Check status
git status

# Stage files
git add file.txt          # Stage single file
git add .                 # Stage all changes

# Commit changes
git commit -m "Commit message"

# Create and switch to branch
git checkout -b branch-name

# Switch branch
git switch branch-name

# View commit history
git log --oneline
git log --graph --all --decorate

# View changes
git diff                  # Unstaged changes
git diff --staged         # Staged changes

# Undo changes
git checkout -- file.txt  # Discard changes
git reset HEAD file.txt   # Unstage file
SHELL,
        'public_content_slug' => 'git-workflow-commands',
        'public_content_index' => false,
    ],

    [
        'name' => 'git-remote-operations.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Add remote
git remote add origin https://github.com/user/repo.git

# List remotes
git remote -v

# Fetch updates
git fetch origin

# Pull from remote
git pull origin main
git pull origin branch-name

# Push to remote
git push origin main
git push origin branch-name

# Push and set upstream
git push -u origin branch-name

# Force push (use with caution)
git push -f origin branch-name

# Delete remote branch
git push origin --delete branch-name

# Merge branch
git merge branch-name

# Rebase
git rebase main
SHELL,
        'public_content_slug' => 'git-remote-operations',
        'public_content_index' => false,
    ],

    // =========================
    // Docker - Basic Commands
    // =========================
    [
        'name' => 'docker-container-operations.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Build image
docker build -t image-name:tag .

# Run container
docker run -d --name container-name -p 8080:80 image-name:tag

# Run with environment variables
docker run -d --name container-name -e ENV_VAR=value image-name:tag

# List running containers
docker ps

# List all containers
docker ps -a

# Stop container
docker stop container-id

# Start container
docker start container-id

# Remove container
docker rm container-id

# View logs
docker logs container-id
docker logs -f container-id    # Follow logs

# Execute command in container
docker exec -it container-id bash
SHELL,
        'public_content_slug' => 'docker-container-operations',
        'public_content_index' => false,
    ],

    [
        'name' => 'docker-compose-commands.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Start services
docker-compose up

# Start in background
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs
docker-compose logs -f service-name

# Execute command in service
docker-compose exec service-name bash

# Build images
docker-compose build

# Rebuild and start
docker-compose up -d --build

# View running services
docker-compose ps

# Remove volumes
docker-compose down -v

# Restart services
docker-compose restart
SHELL,
        'public_content_slug' => 'docker-compose-commands',
        'public_content_index' => false,
    ],

    // =========================
    // System Monitoring
    // =========================
    [
        'name' => 'system-monitoring-commands.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# View running processes
ps aux
ps aux | grep process-name

# Process monitoring with top
top
htop

# Kill process
kill -9 process-id

# Check disk usage
df -h
du -sh directory-name

# Check memory usage
free -h

# View system info
uname -a
lsb_release -a

# CPU information
lscpu
cat /proc/cpuinfo

# Network information
ifconfig
ip addr
netstat -tulpn    # Active connections
SHELL,
        'public_content_slug' => 'system-monitoring-commands',
        'public_content_index' => false,
    ],

    // =========================
    // File Operations
    // =========================
    [
        'name' => 'file-operations-commands.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# List files
ls -la              # List all files with details
ls -lah             # Human readable sizes

# Create directory
mkdir directory-name
mkdir -p path/to/directory

# Remove files
rm file-name
rm -f file-name      # Force remove
rm -r directory      # Remove directory recursively

# Copy files
cp source.txt destination.txt
cp -r source-dir destination-dir

# Move/Rename files
mv old-name new-name
mv file.txt /path/to/destination

# Create file
touch file-name
echo "content" > file-name

# View file content
cat file-name
head -20 file-name  # First 20 lines
tail -20 file-name  # Last 20 lines

# Search in files
grep "pattern" file-name
grep -r "pattern" directory

# Find files
find . -name "*.php"
find . -type f -name "*.log" -delete
SHELL,
        'public_content_slug' => 'file-operations-commands',
        'public_content_index' => false,
    ],

    // =========================
    // Compression & Archives
    // =========================
    [
        'name' => 'compression-commands.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Create tar archive
tar -cf archive.tar file1 file2

# Create compressed tar (gzip)
tar -czf archive.tar.gz directory

# Create compressed tar (bzip2)
tar -cjf archive.tar.bz2 directory

# Extract tar archive
tar -xf archive.tar

# Extract tar.gz
tar -xzf archive.tar.gz

# Extract tar.bz2
tar -xjf archive.tar.bz2

# List tar contents
tar -tf archive.tar
tar -tzf archive.tar.gz

# Create zip archive
zip -r archive.zip directory

# Extract zip
unzip archive.zip

# List zip contents
unzip -l archive.zip

# Gzip file
gzip file-name
gunzip file-name.gz
SHELL,
        'public_content_slug' => 'compression-commands',
        'public_content_index' => false,
    ],
];
