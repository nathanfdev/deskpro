import React, { PropTypes } from 'react';

export class RateAgentForm extends React.Component {

  static propTypes = {
    onSubmit: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      comment: ''
    };
  }

  onSubmit = event => {
    event.preventDefault();
    this.props.onSubmit(this.state.comment);
  };

  onChangeComment = event => {
    this.setState({
      comment: event.target.value
    });
  };

  render() {
    return (
      <div className="dpdesignportal-agent-rating">
        <h1>You have rated <div></div> Noelle Gray as <span className="negative">Not Helpful</span></h1>
        <p className="grey">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>

        <div className="dpdesignportal-agent-rating-form">
          <form className="dpdesignportal-form" onSubmit={this.onSubmit}>
            <label>
              <span className="dpdesignportal-form-item-label-title">How lorel ipsum is the lorel ipsum</span>
              <textarea placeholder="Enter your message here"
                        value={this.state.comment}
                        onChange={this.onChangeComment} />
            </label>

            <div className="label button-label">
                <span className="dpdesignportal-checkbox-container">
                  <span className="dpdesignportal-checkbox"><i className="fa fa-check"></i></span>
                  Share feedback with agent?
                </span>
              <input type="submit" className="dpdesignportal-button" value="Send Feedback" />
              </div>

            </form>
          </div>
        </div>
    );
  }
}
