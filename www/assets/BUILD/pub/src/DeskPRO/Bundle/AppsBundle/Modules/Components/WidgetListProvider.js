import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';

function mapStateToProps(state, ownProps) {
  const { filterAppManifestsConfig } = ownProps;
  const manifests = filterAppManifestsConfig(state);
  return { ...ownProps, manifests };
}

function createWidgetsMapper() {
  let previous = [];

  /**
   * @param {WidgetConfiguration} left
   * @param {WidgetConfiguration} right
   */
  function isSame(left, right) {
    return (
      // here we assume one widget per context
      left.appConfig.instanceId === right.appConfig.instanceId &&
      left.appConfig.bundleUpdatedAt === right.appConfig.bundleUpdatedAt
    );
  }
  function diff(widgetsConfigList)  {
    previous = widgetsConfigList.map((widget) => {
      const previousWidget = previous.filter(other => isSame(other, widget)).pop();
      return previousWidget || widget;
    });
    return previous;
  }

  return diff;
}

export default class WidgetListProvider extends React.PureComponent {
  static propTypes = {
    widgetsConfigList: PropTypes.array.isRequired,
    children:          PropTypes.func.isRequired
  };

  constructor(props)  {
    super(props);
    this.diff = createWidgetsMapper();
  }

  render()  {
    const { widgetsConfigList, children } = this.props;
    return children(this.diff(widgetsConfigList));
  }
}

const ConnectedWidgetListProvider = connect(mapStateToProps)(WidgetListProvider);
export { WidgetListProvider, ConnectedWidgetListProvider };
