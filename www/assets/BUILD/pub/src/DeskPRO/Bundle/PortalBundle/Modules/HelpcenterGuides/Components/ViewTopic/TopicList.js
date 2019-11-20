import PropTypes from 'prop-types';
import React from 'react';
import TopicListItem from './TopicListItem';

class TopicList extends React.Component {
  static propTypes = {
    topics:           PropTypes.array,
    guideSlug:        PropTypes.string,
    topicSlug:        PropTypes.string,
    grabTopicFromApi: PropTypes.func,
    sizes:            PropTypes.object,
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      path:   '',
      filter: '',
    };
  }

  componentDidMount() {
    this.context.router.listen(this.locationHasChanged);
  }

  componentWillUnmount() {
    this.context.router.unregisterTransitionHook(this.locationHasChanged);
  }

  filterTopic = (topic) => {
    let { filter } = this.state;

    filter = filter.toLowerCase();
    return filter === '' || topic.title.toLowerCase().match(filter) || Object.values(topic.children).find(c => this.filterTopic(c));
  };

  handleFilterChange = (e) => {
    this.setState({
      filter: e.target.value
    });
  };

  locationHasChanged = (e) => {
    this.setState({
      path: e.pathname
    });
  };

  render() {
    const { topics, guideSlug, topicSlug, grabTopicFromApi, sizes } = this.props;
    const { filter } = this.state;
    const style = {};
    if (sizes) {
      style.width = sizes.searchWidth;
    }
    return (
      <div className="dp-po-guides-search" style={style}>
        <form className="dp-po-guides-search-form">
          <input type="text" value={filter} placeholder="Search table of contents" onChange={this.handleFilterChange} />
          <button type="submit"><i className="dp-po-icon far fa-search" /></button>
        </form>
        <div className="dp-po-guides-search-block">
          <ul className="dp-po-guides-search-content-list">
            {topics
              .filter(t => t.depth === 0)
              .filter(t => this.filterTopic(t))
              .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
              .map(topic => (
                <TopicListItem
                  key={topic.slug}
                  topic={topic}
                  guideSlug={guideSlug}
                  topicSlug={topicSlug}
                  expandable={false}
                  clickable={false}
                  path={this.state.path}
                  grabTopicFromApi={grabTopicFromApi}
                  filter={filter}
                  filterTopic={this.filterTopic}
                  expanded={(filter !== '' || topic.slug === topicSlug || Object.values(topic.children).find(c => c.slug === topicSlug || Object.values(c.children).find(cc => cc.slug === topicSlug)))}
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
