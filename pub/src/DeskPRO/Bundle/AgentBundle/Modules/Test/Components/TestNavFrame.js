import React from "react";
import TestActions from "../Actions/TestActions";
import * as TaskActions from "../../Tasks/Actions/TaskListActions";
import { connect } from "Ampliflux";

export default class TasksListFrame extends React.Component {
  render() {
    if (!this.props.isLoaded) {
      return (
        <section className="test-nav-frame dp-nav-frame">LOADING</section>
      );
    } else {
      return (
        <section className="test-nav-frame dp-nav-frame">
          X
        </section>
      );
    }
  }
}
