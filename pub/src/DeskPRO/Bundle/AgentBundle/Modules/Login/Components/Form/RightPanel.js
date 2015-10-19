import React from 'react';

export class RightPanel extends React.Component {

  render() {
    return (
      <div className="right-panel">
        <div className="dpw-login-side">
          <a href="https://www.deskpro.com/mobile/" target="_blank" className="dpw-main-banner sample-banner"></a>

          <div className="dpw-login-side-banners">
            <div className="left">
              <a href="https://manuals.deskpro.com/files/agent-quickstart.pdf" target="_blank" className="dpw-login-side-small-banner quick-start"></a>
            </div>

            <div className="right">
              <a href="https://www.deskpro.com/mobile/" target="_blank" className="dpw-login-side-small-banner mobile-apps"></a>
            </div>

          </div>
        </div>
      </div>
    );
  }
}
