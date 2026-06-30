const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const gravityForms = fs.readFileSync(path.join(root, 'inc/integrations/gravity-forms.php'), 'utf8');
const customJs = fs.readFileSync(path.join(root, 'public/js/custom.js'), 'utf8');

assert(
  gravityForms.includes('file_upload_status'),
  'custom Gravity Forms file upload wrapper should include a visible status element'
);

assert(
  !gravityForms.includes('No file selected.'),
  'custom Gravity Forms file upload wrapper should not render an initial no-file message'
);

assert(
  /input\[type=["']file["']\]/.test(customJs) && customJs.includes('file_upload_status'),
  'custom JavaScript should listen for file input changes and update the upload status'
);

assert(
  !customJs.includes('Change event detected'),
  'custom JavaScript should not log every field change to the console'
);

assert(
  gravityForms.includes('window.gform.submission.handleButtonClick'),
  'custom Gravity Forms submit buttons should call Gravity Forms submission handling'
);

assert(
  customJs.includes('is-loading') && customJs.includes('aria-busy'),
  'custom JavaScript should mark custom Gravity Forms submit buttons as loading'
);

assert(
  fs.readFileSync(path.join(root, 'style.css'), 'utf8').includes('.custom-submit.is-loading'),
  'theme stylesheet should style loading custom submit buttons'
);
