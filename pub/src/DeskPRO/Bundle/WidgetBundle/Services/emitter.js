import EventEmitter from 'eventemitter2';
import store from './store';
import { reloadOptions } from '../Modules/Application/Actions/dpWindowActions';
import { reloadSettings } from '../Modules/Application/Actions/bootstrapActions';

const emitter = new EventEmitter();
emitter.on('reloadOptions', options => store.dispatch(reloadOptions(options)));
emitter.on('reloadSettings', settings => store.dispatch(reloadSettings(settings)));

export default emitter;
