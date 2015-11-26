import React from 'react';

export class WidgetHeader extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-header">
        <a href="#" className="dpdesignportal-header-controls left">
          <i className="fa fa-navicon"></i>
        </a>

        <a href="#" className="dpdesignportal-header-controls dpdesignportal-mobile-nav-control right">
          <span className="dpdesignportal-control-hide"><i className="fa fa-times"></i></span>
        </a>

        <div className="dpdesignportal-header-mark">
          <span className="dpdesignportal-logo sample-logo" />
          <h1>Acme Corp. Chat and a long name lorel ipsum dolor</h1>
        </div>
      </div>
    );
  }
}
