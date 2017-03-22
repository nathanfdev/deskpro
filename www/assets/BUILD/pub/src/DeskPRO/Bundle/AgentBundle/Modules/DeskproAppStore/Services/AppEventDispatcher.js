import postRobot from 'post-robot/src';

function dispatchMessage (eventName, message, parentComponent)
{
  const { iframe } = parentComponent;
  postRobot.send(iframe.contentWindow, eventName, message);
}

/**
 * Dispatches an event triggered in the deskpro window to the deskpro app iframe
 */
class AppEventDispatcher
{
  /**
   * @param {Object} message
   * @param parentComponent
   */
  dispatchOnContextInit = (message, parentComponent) =>
  {
    dispatchMessage('context-init', message, parentComponent);
  };

  /**
   * @param {Object} message
   * @param parentComponent
   */
  dispatchOnGetAllState = (message, parentComponent) =>
  {
    dispatchMessage('get-all-state', message, parentComponent);
  };

  /**
   * @param {Object} message
   * @param parentComponent
   */
  dispatchOnGetState = (message, parentComponent) =>
  {
    dispatchMessage('get-state', message, parentComponent);
  };

  /**
   * @param {Object} message
   * @param parentComponent
   */
  dispatchOnSaveState = (message, parentComponent) =>
  {
    dispatchMessage('save-state', message, parentComponent);
  };

}

export default AppEventDispatcher
