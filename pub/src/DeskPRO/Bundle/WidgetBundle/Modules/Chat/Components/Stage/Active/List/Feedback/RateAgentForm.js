import React, { PropTypes } from 'react';

export class RateAgentForm extends React.Component {

  static propTypes = {
    onSubmit: PropTypes.func.isRequired
  };

  onSubmit = event => {
    event.preventDefault();
    this.props.onSubmit();
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
              <textarea placeholder="Enter your message here"></textarea>
            </label>

            <div className="label">
              <span className="dpdesignportal-form-item-label-title">How lorel ipsum is the lorel ipsum</span>
              <div className="dpdesignportal-radio-group dpdesignportal-radio-group-rating">
                <a className="dpdesignportal-radio dpdesignportal-radio-active rate-1" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-2" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-3" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-4" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-5" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-6" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-7" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-8" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-9" href="#"><span></span></a>
                <a className="dpdesignportal-radio rate-10" href="#"><span></span></a>
              </div>
            </div>

            <div className="label">
              <span className="dpdesignportal-form-item-label-title">How lorel ipsum is the lorel ipsum</span>
              <span className="dpdesignportal-form-item-dropdown">Option<i className="fa fa-caret-down"></i></span>
            </div>

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
