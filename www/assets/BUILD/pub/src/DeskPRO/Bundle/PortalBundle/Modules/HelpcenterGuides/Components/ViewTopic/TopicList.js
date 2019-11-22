import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Link } from 'react-scroll';
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

  isExpandedTopic = (topic) => {
    const { topicSlug } = this.props;
    return topic.slug === topicSlug || Object.values(topic.children).find(c => this.isExpandedTopic(c));
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

  renderTopics(topics, depth = 0, collapse = false) {
    const { guideSlug, topicSlug, grabTopicFromApi } = this.props;
    const { filter } = this.state;
    return (
      <ul className={classNames('dp-po-guides-search-content-list', { collapse })}>
        {topics
          .filter(t => depth > 0 || t.depth === depth)
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
              expanded={(filter !== '' || this.isExpandedTopic(topic))}
            />
            )
          )}
      </ul>
    );
  }

  renderList() {
    const { guideSlug, topics } = this.props;
    if (window.twoLevelSection) {
      let baseUrl = window.DESKPRO_BASE_URL;
      if (baseUrl) {
        baseUrl = baseUrl.replace(/\/+$/, '');
      }

      return (
        <div className="dp-po-guides-search-content accordion" id="accordionExample">
          {topics
            .filter(t => t.depth === 0)
            .filter(t => this.filterTopic(t))
            .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
            .map((topic) => {
              const collapsed = !this.isExpandedTopic(topic);
              return (
                <div className="dp-po-guides-search-content-accordion" key={topic.slug}>
                  <Link
                    className={classNames('dp-po-guides-search-content-title', { collapsed })}
                    activeClass="active"
                    href={`${baseUrl}/guides/${guideSlug}/${topic.slug}`}
                    to={`topic_${topic.slug}`}
                    offset={-178}
                    spy
                    smooth
                    isDynamic
                    onClick={this.handleClick}
                    onSetActive={this.handleSetActive}
                  >
                    {topic.title} <i className="dp-po-icon far fa-angle-down" />
                  </Link>
                  {this.renderTopics(Object.values(topic.children), 1, collapsed)}
                </div>
              );
            }
          )}
        </div>
      );
    }
    return this.renderTopics(topics);
  }

  render() {
    const { sizes } = this.props;
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
          {this.renderList()}
        </div>
      </div>
    );
  }
}
export default TopicList;
