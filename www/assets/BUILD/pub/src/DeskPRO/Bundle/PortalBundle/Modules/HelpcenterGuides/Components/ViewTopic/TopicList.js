import PropTypes from 'prop-types';
import React from 'react';
import Topic from './Topic';

class TopicList extends React.Component {
  static propTypes = {
    topics:    PropTypes.object,
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
    const { topics, guideSlug } = this.props;
    return (
      <div className="dp-po-guides-search">
        <form className="dp-po-guides-search-form">
          <input type="text" name="" placeholder="Search table of contents" />
          <button type="submit"><i className="dp-po-icon far fa-search" /></button>
        </form>
        <div className="dp-po-guides-search-block">
          <ul className="dp-po-guides-search-content-list">
            {Object.values(topics)
              .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
              .map(topic => (
                <Topic
                  key={topic.slug}
                  topic={topic}
                  guideSlug={guideSlug}
                  expandable={false}
                  clickable={false}
                  path={this.state.path}
                  level={0}
                />
              )
            )}
          </ul>
        </div>
      </div>
    );
  }
}
export default TopicList;
