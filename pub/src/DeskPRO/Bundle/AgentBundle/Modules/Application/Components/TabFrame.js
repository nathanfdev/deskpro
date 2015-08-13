import React from "react";

export default class TabFrame extends React.Component {
  render() {
    return (
      <section className="dp-tab-frame">
        <div className="blank-text">
          <p className="hero-icon">
            <i className="fa fa-file-o"></i>
          </p>
          <p>
            No tabs open yet; select an item to display the details here.
          </p>
        </div>
      </section>
    );
  }
}
