import React from "react"
import PortalUrlGenerator from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator"

export default class ContactUsDropdown extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
        hovering: false
    };
  }
  onMouseOver() {
    this.setState({
      hovering: true
    });
  }
  onMouseOut() {
    this.setState({
      hovering: false
    });
  }
  render() {
    let style = { display: "none" };
    if (this.state.hovering) {
      style = {};
    }
    return (
      <div onMouseOver={this.onMouseOver.bind(this)} onMouseOut={this.onMouseOut.bind(this)}>
        <a href={PortalUrlGenerator.path('/new-ticket')}>
         <span className="button select-action">
          <i className="fa fa-comments"></i> Contact Us
          <span className="select-action-dropdown">
            <i className="fa fa-caret-down"></i>
          </span>
        </span>
        </a>

        <div className="dropdown-content" style={style}>
          <ul>
            <li>
              <a href={PortalUrlGenerator.path('/new-ticket')}>
                <i className="fa fa-comments"></i>
                <h1>Get in touch</h1>
                <p>With this excellent form</p>
              </a>
            </li>

            <li>
              <a href={PortalUrlGenerator.path('/feedback')}>
                <i className="fa fa-list-alt"></i>
                <h1>Submit Feedback</h1>
                <p>An excellent feedback system</p>
              </a>
            </li>

            <li>
              <a href="#">
                <i className="fa fa-comment"></i>
                <h1>Start a chat session</h1>
                <p><span className="online-disc"></span> 3 Agents Available</p>
              </a>
            </li>
          </ul>
        </div>
      </div>
    );
  }
}
