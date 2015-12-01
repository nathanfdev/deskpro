import React from 'react';

export class WidgetFooter extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-powered-by-deskpro">
        <a href="http://deskpro.com/" target="_blank">
          <hr/> Support powered by <span className="dpdesignportal-deskpro-mark-logo"></span> <hr/>
        </a>
      </div>
    );
  }
}
