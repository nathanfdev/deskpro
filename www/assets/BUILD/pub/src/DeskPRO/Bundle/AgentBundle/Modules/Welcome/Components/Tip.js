import React from 'react';

export class Tip extends React.Component {

  render() {
    return (
      <div className="deskpro-tip">
        <div className="deskpro-loading-tip-controls">
          <a href="#" className="back"><i className="fa fa-angle-left"></i></a>
          <a href="#" className="forward"><i className="fa fa-angle-right"></i></a>
        </div>
        <div className="deskpro-loading-tip-icon"><i className="fa fa-tags"></i></div>
        <h1>Did you know?</h1>
        <p>But in certain circumstances and owing to the claims of duty or the obligations of business it will frequently occur that. Lorel ipsum dolor sit amet.</p>
        <div className="deskpro-loading-footer">
          <a href="#" className="left"><i className="fa fa-thumbs-o-up"></i> Mark this tip as useful</a>
          <a href="#" className="right"><i className="fa fa-external-link"></i> Learn More</a>
        </div>
      </div>
    );
  }

}
