import React from "react"
import _ from "lodash"

export default class StatusCategory extends React.Component {
  clicked(e) {
    e.preventDefault();
    this.props.setStatusCategory(this.props.cat.id);
  }

  render() {
    return (
      <div className="cat-checkbox-title">
        <input type="checkbox" checked={this.props.isActive} onChange={this.clicked.bind(this)}/>
        <a style={this.props.isActive ? {} : {}} onClick={this.clicked.bind(this)}>
          {this.props.cat.title}
        </a>
      </div>
    );
  }
}
