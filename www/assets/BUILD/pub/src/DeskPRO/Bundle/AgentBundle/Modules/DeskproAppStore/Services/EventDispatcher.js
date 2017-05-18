import { EventEmitter } from 'eventemitter3';


import postRobot from 'post-robot/src';

function dispatchMessage (eventName, message, parentComponent)
{
  const { iframe } = parentComponent;
  postRobot.send(iframe.contentWindow, eventName, message);
}

export class EventDispatcher extends EventEmitter
{

}

export const RequestEventDispatcher = new EventDispatcher();

export const ResponseEventDispatcher = new EventDispatcher();

