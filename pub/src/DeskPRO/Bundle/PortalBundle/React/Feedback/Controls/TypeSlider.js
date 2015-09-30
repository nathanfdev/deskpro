import React from "react"
import _ from "lodash"

export default class TypeSlider extends React.Component {
  click(e) {
    e.preventDefault();
    this.props.toggleType(this.props.id);
  }

  render() {
    return (
      <li>
        <div className="slider-panel">
          <a href={'/feedback/browse/type-' + this.props.id}
             className={this.props.active ? "slider" : "slider off"}
             onClick={this.click.bind(this)}>
            <span className="slider-status">{this.props.active ? "on" : "off"}</span>
            <span className="slider-icon"><i className="fa fa-check"></i></span>
          </a>
          <span className="slider-label" onClick={this.click.bind(this)}>
            {this.props.label}
          </span>
        </div>
      </li>
    );
  }
}
