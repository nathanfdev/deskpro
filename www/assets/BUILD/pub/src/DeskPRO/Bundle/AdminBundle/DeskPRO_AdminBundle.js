import AdminApp from './AdminApp';
import { DeskproAppsMain } from './Modules/DeskproApps/DeskproAppsMain';

window.AdminBundle = AdminApp;
// bootstrap deskpro apps
DeskproAppsMain.main(window);

