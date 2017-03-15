import postRobot from 'post-robot/src';

function dispatchMessage (eventName, message, parentComponent)
{
  const { iframe } = parentComponent;
  postRobot.send(iframe.contentWindow, eventName, message);
}

/**
 * Dispatches an event triggered in the deskpro window to the deskpro app iframe
 */
class DeskproEventDispatcher
{
  /**
   * @param {Object} message
   * @param parentComponent
   */
  dispatchOnContextInit = (message, parentComponent) =>
  {
    dispatchMessage('context-init', message, parentComponent);
  };

}

export default DeskproEventDispatcher
