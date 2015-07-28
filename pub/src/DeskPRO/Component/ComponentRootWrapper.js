import React from "react";

import $ from "jquery";

export default class ComponentRootWrapper extends React.Component {
    render() {
        return (<div/>);
    }

    componentDidMount() {
        this.node = React.findDOMNode(this);
        $(this.node).detach();
        $('body').append(this.node);

        // Manipulate the DOM here
        this.renderDialogContent();
    }

    componentWillReceiveProps(newProps) {
        // Re-render the dialog box with the new properties when there's a change
        this.renderDialogContent(newProps);
    }

    renderDialogContent(props) {
        props = props || this.props;

        // Render the component with react
        React.render(props.children, this.node);

        // Can show and hide a node depending on the open property
        if (props.open) {
            $(this.node).show();
        } else {
            $(this.node).hide();
        }
    }

    componentWillUnmount() {
        // Clean up the DOM when the component is umounted
        React.unmountComponentAtNode(this.node);
        $(this.node).remove();
    }
}
