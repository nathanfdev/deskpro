import 'react-hot-loader/patch';
import AgentLegacyApp from './AgentLegacyApp';

const app = new AgentLegacyApp();
app.run();

window.AgentLegacyBundle = app;
