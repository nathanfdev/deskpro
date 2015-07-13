import React from "react";

export default class ListFrame extends React.Component {
  render() {
    return (<section className="dp-list-frame">
      {this.props.children}
    </section>);
  }
}
