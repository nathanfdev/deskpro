import React from 'react';

export class FeedbackCard extends React.Component {

  render() {
    const {feedback} = this.props;
    return (
      <div className="dpmw--single-card">

        <div className="dpm--card-checkbox">
          <i className="fa fa-check"></i>
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">
            <div className="dpwd--card-title">
              <h1>{feedback.title}</h1>
            </div>
          </div>

          <div className="dpw--card-line-right">
            <div className="dpwd--card-assigned">
              <span className="dpw--avatar-face" style={{backgroundImage: 'url(../img/avatars/avatar6.png)'}}></span>
            </div>
          </div>
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">
            <div className="dpwd--card-title">
              <h1>{feedback.content}</h1>
            </div>
          </div>
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">

            <span className="dpwd--card-line-item">
              <i className="fa fa-calendar-o"></i> Created: {feedback.date_created}
            </span>

            <span className="dpw--card-disc"></span>

            <span className="dpwd--card-line-item">
              <i className="fa fa-book"></i> {feedback.category}
            </span>

            <span className="dpw--card-disc"></span>

            <span className="dpwd--card-line-item">
              <i className="fa fa-link"></i> <a href="#">Linked ticket</a>
            </span>
          </div>

          <div className="dpw--card-line-right">
            <span className="dpwd--card-line-item">
              5 <i className="fa fa-comment"></i>
            </span>

            <span className="dpw--card-disc"></span>

            <span className="dpwd--card-line-item">
                <div>1/3 <i className="fa fa-folder-open"></i></div>
            </span>
          </div>
        </div>
      </div>
    );
  }
}
