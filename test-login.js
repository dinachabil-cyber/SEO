const fetch = require('node-fetch');

const login = async () => {
    try {
        // First, get the login page to get any cookies or CSRF tokens
        const response1 = await fetch('https://seo-project.ddev.site/login');
        const cookies = response1.headers.get('set-cookie');
        console.log('Cookies:', cookies);
        
        // Now, send the login request
        const response2 = await fetch('https://seo-project.ddev.site/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cookie': cookies,
            },
            body: '_username=admin&_password=admin123&_remember_me=on&_target_path=/admin/site',
        });
        
        console.log('Status:', response2.status);
        console.log('Headers:', response2.headers.raw());
        console.log('Body:', await response2.text());
    } catch (error) {
        console.error('Error:', error);
    }
};

login();
