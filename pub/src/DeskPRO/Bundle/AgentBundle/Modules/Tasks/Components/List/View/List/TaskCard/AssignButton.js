import React from 'react';

export class AssignButton extends React.Component {

  render() {
    return (
      <div className="dpwd--card-assigned">
        <div className="dpw--avatar-face" style={{position: 'relative'}}>
          <i className="fa fa-caret-down" />
        </div>
      </div>
    );
  }
}
