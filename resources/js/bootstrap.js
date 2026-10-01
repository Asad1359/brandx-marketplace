import axios from 'axios';

/*
|--------------------------------------------------------------------------
| AXIOS
|--------------------------------------------------------------------------
| Echo ab sirf `resources/js/echo.js` mein setup hai (Laravel Reverb).
| Yahan koi Echo/Pusher code nahi hona chahiye.
|--------------------------------------------------------------------------
*/

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] =
    'XMLHttpRequest';

window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;