const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');

const app = express();
app.use(express.json());

const fs = require('fs');
const path = require('path');

// Helper to auto-detect Chrome installation path on Windows
function getChromePath() {
    const paths = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        path.join(process.env.USERPROFILE || '', 'AppData\\Local\\Google\\Chrome\\Application\\chrome.exe')
    ];

    for (const chromePath of paths) {
        if (fs.existsSync(chromePath)) {
            return chromePath;
        }
    }
    return null;
}

const chromePath = getChromePath();
if (chromePath) {
    console.log(`Menggunakan browser Chrome lokal: ${chromePath}`);
} else {
    console.log('Tidak mendeteksi Chrome lokal. Menggunakan setelan bawaan Puppeteer.');
}

// Initialize WhatsApp Web Client with Local authentication session storage
const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: './wa_session'
    }),
    puppeteer: {
        executablePath: chromePath || undefined, // Use local Chrome installation if available
        handleSIGINT: false,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--single-process', // Share processes to reduce RAM consumption
            '--disable-gpu'
        ]
    }
});

// Event: QR Code generation
client.on('qr', (qr) => {
    console.clear();
    console.log('==================================================================');
    console.log('SCAN QR CODE DI BAWAH INI MENGGUNAKAN WHATSAPP DI PONSEL ANDA');
    console.log('==================================================================\n');
    qrcode.generate(qr, { small: true });
    console.log('\nJika QR code di atas tidak terlihat rapi, perbesar jendela terminal Anda.');
});

// Event: Successfully authenticated & ready
client.on('ready', () => {
    console.clear();
    console.log('==================================================================');
    console.log('WHATSAPP GATEWAY BERHASIL DIHUBUNGKAN DAN SIAP DIGUNAKAN!');
    console.log('==================================================================');
    console.log('Mendengarkan permintaan kirim pesan pada http://localhost:8000/send-message');
});

// Event: Auth failure
client.on('auth_failure', (msg) => {
    console.error('Gagal melakukan autentikasi sesi WhatsApp:', msg);
});

// Event: Disconnected
client.on('disconnected', (reason) => {
    console.log('WhatsApp terputus:', reason);
    console.log('Mencoba menghubungkan kembali...');
    client.initialize();
});

// API Endpoint: Send Message
app.post('/send-message', async (req, res) => {
    const { number, message } = req.body;

    if (!number || !message) {
        return res.status(400).json({ 
            success: false, 
            error: 'Parameter "number" dan "message" wajib diisi.' 
        });
    }

    try {
        // Format number to international format without characters (628xxxxxxxx)
        let formattedNumber = number.replace(/[^0-9]/g, '');
        if (formattedNumber.startsWith('08')) {
            formattedNumber = '628' + formattedNumber.substring(2);
        }
        
        // Append @c.us suffix required by whatsapp-web.js
        if (!formattedNumber.endsWith('@c.us')) {
            formattedNumber = formattedNumber + '@c.us';
        }

        // Check if number is registered on WhatsApp
        const isRegistered = await client.isRegisteredUser(formattedNumber);
        if (!isRegistered) {
            return res.status(400).json({ 
                success: false, 
                error: 'Nomor tujuan tidak terdaftar di WhatsApp.' 
            });
        }

        // Send message
        const response = await client.sendMessage(formattedNumber, message);
        
        res.status(200).json({ 
            success: true, 
            status: 'success', 
            messageId: response.id.id 
        });
        
        console.log(`[${new Date().toLocaleTimeString()}] Berhasil mengirim pesan ke ${number}`);
    } catch (error) {
        console.error('Gagal mengirim pesan:', error);
        res.status(500).json({ 
            success: false, 
            error: error.message 
        });
    }
});

// Initialize client connection
console.log('Sedang menyiapkan modul WhatsApp, mohon tunggu...');
client.initialize();

const PORT = 8000;
app.listen(PORT, () => {
    console.log(`Server API WA Gateway berjalan pada port ${PORT}`);
});
