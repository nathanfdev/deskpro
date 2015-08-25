import React from 'react';
import { connect } from 'redux/react';

@connect(state => state.FeedbackList)

export class FeedbackCard extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        const {feedback} = this.props;
        return (
            <div className="card feedback-card">
                <div className="card-status-bar status-bar-left level-8"></div>
                <div className="card-status-bar status-bar-right level-8"></div>

                <div className="card-checkbox">
                    <span className="checkbox"><i className="fa fa-check"></i></span>
                </div>

                <div className="card-line">
                  <span className="line-box">
                    <span className="feedback-id">#{feedback.id}</span>
                  </span>
                    <span className="line-box card-feedback-mark">
                    <i className="fa fa-thumbs-up"></i><span className="feedback-count">{feedback.popularity}</span>
                  </span>

                    <h1>{feedback.title}</h1>
                </div>

                <div className="card-line">
                    <div className="ticket-intro">
                        <p>{feedback.content}</p>
                    </div>
                </div>

                <div className="card-line">
                    <div className="task-extras">
                        <span className="text">{feedback.author_name}</span>
                        <span className="chat-avatar" style={{backgroundImage: "url('./img/avatar6.png')"}}></span>
                        <span className="disc"></span>
                        <span className="text">{feedback.num_comments}</span> <i className="fa fa-comment"></i>
                    </div>

                    <div className="task-properties">
                        <i className="fa fa-book"></i> <span className="feedback-type">{feedback.type}</span>
                        <span className="disc"></span>
                        <i className="fa fa-book"></i> <span
                        className="feedback-custom-category">{feedback.custom_category}</span>
                        <span className="disc"></span>
                    </div>
                </div>
            </div>
        );
    }
}
