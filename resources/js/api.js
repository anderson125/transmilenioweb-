import axios from 'axios';
import router from './routes/index';
import toastr from 'toastr'

const defaultOptions = {
    baseURL: process.env.MIX_APP_URL,
    headers: {
        'Accept': 'application/json',
    },
};
var instance = axios.create(defaultOptions);
instance.interceptors.request.use(function (config) {
    const token = sessionStorage.getItem('token');
    config.headers.Authorization = token ? `Bearer ${token}` : '';
    return config;
});

instance.interceptors.response.use(null, error => {
    toastr.options.progressBar = true;

    if(error?.response?.status){
        switch (error.response.status) {
            case 401:
                toastr.warning('Usuario no autorizado')
                router.push('/login');
                break;
            case 404:
                toastr.warning('Dato no encontrado')
                break;
            case 500:
                toastr.warning('Error del servidor')
                break;
            case 400:
            case 422:
                toastr.options.timeOut = 0;
                toastr.options.extendedTimeOut = 0;
                toastr.options.closeButton = true;
                toastr.options.preventDuplicates = true;
                let err = error.response.data.response.errors
                let message = '<ul>'
                err.forEach(element => {
                    message = '<li>' + element + '</li>'
                });
                message += '</ul>'
                toastr.warning(message, 'Error en el envio del formulario')
                break;
            case 405:
                toastr.warning('Metodo de envio no permitido')
                break;
        }
    }else{
        error.response = { status : 500 };
    }
    

    return Promise.reject(error);
});
export default instance;