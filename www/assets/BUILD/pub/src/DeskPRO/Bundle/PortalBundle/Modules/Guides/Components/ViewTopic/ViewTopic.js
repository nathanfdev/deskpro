import React, { PropTypes } from 'react';
import Highlight from 'react-highlight';
import classNames from 'classnames';
import moment from 'moment';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import TopicList from './TopicList';

class ViewTopic extends React.Component {
  static propTypes = {
    params: PropTypes.object
  };

  constructor(props) {
    super(props);
    let topic = null;
    if (window.topic) {
      topic = JSON.parse(window.topic);
    }
    this.state = {
      doSpin: false,
      topic,
      guide:  {
        title: ''
      }
    };
  }

  componentDidMount() {
    if (this.props.params.splat) {
      this.grabGuideFromApi(this.getGuideSlug(this.props.params.splat));
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.params.slug !== this.props.params.slug) {
      this.grabTopicFromApi(nextProps.params.slug);
    }
    const nextGuideSlug = this.getGuideSlug(nextProps.params.splat);
    if (nextGuideSlug !== this.getGuideSlug(this.props.params.splat)) {
      this.grabGuideFromApi(nextGuideSlug);
    }
  }

  getGuideSlug = splat => splat.split('/')[0];

  grabGuideFromApi(slug) {
    portalHttp.sendGet(`DP_URL/portal/api/guides/guide/${slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      this.setState({
        guide: response.data.data,
      });
    });
  }

  grabTopicFromApi(slug) {
    this.setState({
      doSpin: true
    });

    portalHttp.sendGet(`DP_URL/portal/api/guides/topic/${slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      this.setState({
        doSpin: false,
        topic:  response.data.data,
      });
    });
  }

  render() {
    const topics = JSON.parse(window.topicList);
    const { locale, splat } = this.props.params;
    const guideSlug = this.getGuideSlug(splat);
    return (
      <div>
        <div className="topic-list">
          <div className="guide">{this.state.guide.title}</div>
          <hr />
          <TopicList topics={topics} locale={locale} guideSlug={guideSlug} />
        </div>
        <div className="topic">
          <div className={classNames('loading', { active: this.state.doSpin || !this.state.topic.slug })} />
          <header className="section-header">
            <h1>{this.state.topic.title}</h1>
            <span id="publication-date" className="publication_date">
              <label htmlFor="publication-date">Published: </label>
              {moment(this.state.topic.date_published).format('DD/MM/YYYY')}
            </span>
            <span id="last-update-date" className="last_update_date">
              <label htmlFor="last-update-date">Updated: </label>
              {moment(this.state.topic.date_updated).format('DD/MM/YYYY')}
            </span>
            <hr />
          </header>
          <div className="topic-content">
            <Highlight innerHTML>
              {this.state.topic.content}
            </Highlight>
          </div>
        </div>
      </div>
    );
  }
}

export default ViewTopic;
