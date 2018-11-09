import '@deskpro/apps-components-style'; // eslint-disable-line import/extensions

import PropTypes from 'prop-types';
import React from 'react';

import { WidgetContainer } from './WidgetContainer';
import {  WidgetConfiguration } from '../Domain';
import { WidgetContainerLegacy } from './WidgetContainerLegacy';

export class WidgetContainerList extends React.PureComponent {
  static propTypes = {
    /**
     * the list of configurations of each widget
     */
    widgets: PropTypes.arrayOf(WidgetConfiguration).isRequired,

    /**
     * The id of the widget currently in fullscreen
     */
    widgetFullscreen: PropTypes.string,

    /**
     * If this list should be visible or not
     */
    isVisible: PropTypes.bool,

    /**
     * whether to render the legacy app container
     */
    withLegacyAppContainer: PropTypes.bool,

    /**
     * maps of props that should be passed to each widget
     */
    widgetProps: PropTypes.shape({
      /**
       * function that returns the current message sent from the underlying iframe
       */
      getEvent: PropTypes.func.isRequired,

      /**
       * function that returns a map of event providers each widget should use to configure its event subscriptions
       */
      getEventProviders: PropTypes.func.isRequired,

      /**
       * callback that is invoked when each widget is unmounted
       */
      unregister: PropTypes.func.isRequired,

    }).isRequired,
  };

  static defaultProps = {
    isVisible:              true,
    withLegacyAppContainer: false,
  };

  constructor(props) {
    super(props);
    this.wrapperRef = React.createRef();
  }

  componentDidUpdate()  {
    const node = this.wrapperRef.current;
    if (this.props.isVisible) {
      node.style.visibility = 'visible';
      node.style.position = 'static';
    } else {
      node.style.visibility = 'hidden';
      node.style.position = 'absolute';
    }
  }

  renderWidget = configuration =>  (<WidgetContainer
    key={`widget-${configuration.id}`}
    {...this.props.widgetProps}
    isFullscreen={this.props.widgetFullscreen === configuration.id}
    configuration={configuration}
  />);

  render()  {
    return (<div className={'layout-sidebar--stretch-vertical layout-sidebar__widget-list'} ref={this.wrapperRef}>
      { this.props.widgets.map(this.renderWidget) }
      { this.props.withLegacyAppContainer && <WidgetContainerLegacy /> }
    </div>);
  }
}
