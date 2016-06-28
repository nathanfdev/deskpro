import { createSelector } from 'reselect';

const infoSel = createSelector(
    state => state.Application.dpWindow.get('winDims'),
    state => state.Application.dpWindow.get('columnMode'),
    state => state.Application.dpWindow.get('columnDimensions'),
    state => state.Application.dpWindow.get('sidebarMode'),
  (winDims, columnMode, columnDimensions, sidebarMode, collapseNav) => ({
    winDims: winDims.toJS(), columnMode, columnDimensions, sidebarMode, collapseNav
  })
);

export const workspaceSelector = createSelector(
  infoSel,
  (info) => {
    const workspace = {
      // appPane is wrapper around appNavPane and appListPane
      // we dont ever turn it completely off because the UI for at least
      // the nav pane can still be visible by hoving mouse
      appPane:     true,
      appNavPane:  true,
      appListPane: true,

      // Width of the appPane
      appPaneWidth: 50,

      // tabPane also never turns off
      tabPane: true
    };

    if (info.columnMode !== 'column') {
      workspace.appListPane = false;
    }
    if (info.sidebarMode !== 'static') {
      workspace.appNavPane = false;
    }
    workspace.appListWidth = parseInt(info.columnDimensions, 10) || 50;

    return workspace;
  }
);

export const workspaceDimsSelector = createSelector(
  infoSel,
  workspaceSelector,
  (info, workspace) => {
    const dims = {
      appPaneSize: 0,
      appNavSize:  0,
      appListSize: 0,
      tabPaneSize: 0
    };

    // Constants
    const appSwitcherWidth = 55;
    const appNavWidth = 220;
    const appNavClosedWidth = 10;

    // Current window size
    const winWidth = info.winDims.width;

    // The actual width we have to play with
    const middleWidth = winWidth - appSwitcherWidth;

    if (!workspace.appPane) {
      dims.appNavSize = appNavClosedWidth;
      dims.appListSize = 0;
    } else {
      if (workspace.appNavPane && workspace.appListPane) {
        dims.appNavSize = appNavWidth;
        dims.appListSize = Math.ceil(middleWidth * workspace.appListWidth / 100);
      } else if (workspace.appNavPane) {
        dims.appNavSize = appNavWidth;
        dims.appListSize = 0;
      } else if (workspace.appListPane) {
        dims.appNavSize = appNavClosedWidth;
        dims.appListSize = Math.ceil(middleWidth * workspace.appListWidth / 100);
      } else {
        dims.appNavSize = appNavClosedWidth;
        dims.appListSize = 0;
      }

      dims.appPaneSize = dims.appNavSize + dims.appListSize;
    }

    dims.tabPaneSize = middleWidth - dims.appPaneSize;

    return dims;
  }
);
