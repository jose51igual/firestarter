module.exports = {
  apps: [
    {
      name: 'firestarter',
      script: 'node_modules/next/dist/bin/next',
      args: 'start',
      cwd: '/var/www/firestarter',
      instances: 'max',
      exec_mode: 'cluster',
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: 3000
      },
      env_production: {
        NODE_ENV: 'production',
        PORT: 3000
      }
    }
  ]
};
