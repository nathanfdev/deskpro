import 'react-hot-loader/patch';
import { portalApp } from './PortalApp';

portalApp.run();

window.PortalBundle = portalApp;
