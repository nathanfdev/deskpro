import React from 'react';
import ReactDOM from 'react-dom';

import jQuery from 'jquery';

export default class ComponentRootWrapper extends React.Component {

  componentDidMount() {
    this.node = ReactDOM.findDOMNode(this);
    jQuery(this.node).detach();
    jQuery('body').append(this.node);

    // Manipulate the DOM here
    this.renderDialogContent();
  }

  componentWillReceiveProps(newProps) {
    // Re-render the dialog box with the new properties when there's a change
    this.renderDialogContent(newProps);
  }

  componentWillUnmount() {
    // Clean up the DOM when the component is umounted
    ReactDOM.unmountComponentAtNode(this.node);
    jQuery(this.node).remove();
  }

  renderDialogContent(props) {
    const componentProps = props || this.props;

    const renderSubtreeIntoContainer = ReactDOM.unstable_renderSubtreeIntoContainer;

    // Render the component with react
    renderSubtreeIntoContainer(this, componentProps.children, this.node);

    // Can show and hide a node depending on the open property
    if (componentProps.open) {
      jQuery(this.node).show();
    } else {
      jQuery(this.node).hide();
    }
  }

  render() {
    return (<div/>);
  }
}
