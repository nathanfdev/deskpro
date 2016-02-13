import React from 'react';

export class PasteCatcher extends React.Component {

  render() {
    return (
      <div className="paste-catcher"
           contentEditable="true"
           style={{
             position: 'absolute',
             left: -999,
             width: 0,
             height: 0,
             overflow: 'hidden',
             outline: 0
           }} />
    );
  }
}
