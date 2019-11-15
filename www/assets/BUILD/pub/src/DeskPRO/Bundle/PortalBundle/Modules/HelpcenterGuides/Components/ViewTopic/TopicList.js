import PropTypes from 'prop-types';
import React from 'react';
import TopicListItem from './TopicListItem';

class TopicList extends React.Component {
  static propTypes = {
    topics:           PropTypes.array,
    guideSlug:        PropTypes.string,
    grabTopicFromApi: PropTypes.func,
    sizes:            PropTypes.object,
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
    const { topics, guideSlug, grabTopicFromApi, sizes } = this.props;
    const style = {};
    if (sizes) {
      style.width = sizes.searchWidth;
    }
    return (
      <div className="dp-po-guides-search" style={style}>
        {/* <form className="dp-po-guides-search-form">*/}
        {/*  <input type="text" name="" placeholder="Search table of contents" />*/}
        {/*  <button type="submit"><i className="dp-po-icon far fa-search" /></button>*/}
        {/* </form>*/}
        <div className="dp-po-guides-search-block">
          <ul className="dp-po-guides-search-content-list">
            {topics
              .filter(a => a.depth === 0)
              .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
              .map(topic => (
                <TopicListItem
                  key={topic.slug}
                  topic={topic}
                  guideSlug={guideSlug}
                  expandable={false}
                  clickable={false}
                  path={this.state.path}
                  grabTopicFromApi={grabTopicFromApi}
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
