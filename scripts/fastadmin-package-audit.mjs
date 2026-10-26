import fs from 'node:fs';

const OFFICIAL_FASTADMIN_COMMIT = 'af2d02eb08a6b1bb712a1b8d7fc33348705b6889';

const requiredFiles = [
  'public/assets/css/backend.css',
  'public/assets/css/backend.min.css',
  'public/assets/css/bootstrap.css',
  'public/assets/img/login-head.png',
  'public/assets/img/avatar.png',
  'public/assets/js/require.min.js',
  'public/assets/js/require-backend.js',
  'public/assets/js/require-backend.min.js',
  'public/assets/fonts/glyphicons-halflings-regular.woff2',
  'public/assets/libs/jquery/dist/jquery.min.js',
  'public/assets/libs/bootstrap/dist/js/bootstrap.min.js',
  'public/assets/libs/fastadmin-layer/dist/layer.js',
  'public/assets/libs/nice-validator/dist/jquery.validator.js',
  'public/assets/libs/fastadmin-selectpage/selectpage.js',
  'public/assets/libs/font-awesome/css/font-awesome.min.css',
  'public/assets/libs/moment/moment.js',
  'public/assets/libs/require-css/css.min.js',
  'addons/.htaccess',
  'runtime/.htaccess',
  'public/uploads/.htaccess',
  'public/admin1.php',
];

const expectedSecurityFiles = new Map([
  ['addons/.htaccess', 'deny from all'],
  ['runtime/.htaccess', 'deny from all'],
  [
    'public/uploads/.htaccess',
    '<FilesMatch \\.(?i:html|php)$>\n  Order allow,deny\n  Deny from all\n</FilesMatch>',
  ],
]);

const failures = [];

for (const file of requiredFiles) {
  if (!fs.existsSync(file)) {
    failures.push(`missing required FastAdmin runtime file: ${file}`);
  }
}

for (const [file, expected] of expectedSecurityFiles) {
  if (!fs.existsSync(file)) continue;
  const actual = fs.readFileSync(file, 'utf8').replace(/\r\n/g, '\n').trim();
  if (actual !== expected) {
    failures.push(
      `${file} no longer matches FastAdmin ${OFFICIAL_FASTADMIN_COMMIT}`,
    );
  }
}

if (fs.existsSync('public/admin.php')) {
  failures.push(
    'public/admin.php must stay absent; this project intentionally uses public/admin1.php',
  );
}

if (failures.length > 0) {
  console.error('FastAdmin package audit failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log(
  `FastAdmin package audit OK: ${requiredFiles.length} required files present; security files match ${OFFICIAL_FASTADMIN_COMMIT}; public/admin.php remains absent.`,
);
