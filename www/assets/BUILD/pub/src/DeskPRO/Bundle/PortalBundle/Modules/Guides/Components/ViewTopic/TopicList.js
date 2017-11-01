import PropTypes from 'prop-types';
import React from 'react';
import Topic from './Topic';

class TopicList extends React.Component {
  static propTypes = {
    topics:    PropTypes.object,
    locale:    PropTypes.string,
    guideSlug: PropTypes.string
  };
  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      path: ''
    };
  }

  componentDidMount() {
    this.context.router.listen(this.locationHasChanged);
  }

  componentWillUnmount() {
    this.context.router.unregisterTransitionHook(this.locationHasChanged);
  }

  locationHasChanged = (e) => {
    this.setState({
      path: e.pathname
    });
  };

  render() {
    const { locale, topics, guideSlug } = this.props;
    return (<ul>
      {Object.values(topics)
        .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
        .map(topic => (
          <Topic
            key={topic.slug}
            topic={topic}
            locale={locale}
            guideSlug={guideSlug}
            expandable={false}
            clickable={false}
            path={this.state.path}
          />
        )
      )}
    </ul>);
  }
}
export default TopicList;
