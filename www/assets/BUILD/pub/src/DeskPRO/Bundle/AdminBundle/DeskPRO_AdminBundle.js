import 'react-hot-loader/patch';
import AdminApp from './AdminApp';
import { DeskproAppsMain } from './Modules/DeskproApps/DeskproAppsMain';

const app = new AdminApp();
app.run();
window.AdminBundle = AdminApp;
// bootstrap deskpro apps
DeskproAppsMain.main(window);

