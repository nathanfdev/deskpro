import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import browserHistory from 'react-router/lib/browserHistory';
import { TopicList, TopicSummary, GuideSelector } from '../index';

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
    topic.content = this.addIdToh1(topic.content);
    const topics = JSON.parse(window.topicList);
    this.state = {
      doSpin:    false,
      topic,
      guideSlug: this.getGuideSlug(this.props.params.splat),
      topics
    };
  }

  componentDidMount() {
    this.changeInternalLinks();
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.params.slug !== this.props.params.slug) {
      this.grabTopicFromApi(nextProps.params.slug);
    }
    const nextGuideSlug = this.getGuideSlug(nextProps.params.splat);
    if (nextGuideSlug !== this.getGuideSlug(this.props.params.splat)) {
      this.setState({
        guideSlug: nextGuideSlug
      });
    }
  }

  componentDidUpdate() {
    this.changeInternalLinks();
  }

  getGuideSlug = splat => splat.split('/')[0];

  changeInternalLinks = () => {
    document.querySelectorAll('a.internal_link.topic').forEach((internalLink) => {
      const newLink = document.createElement('a');
      newLink.className = 'internal_link topic';
      newLink.onclick = e => this.internalLink(e, internalLink.pathname);
      newLink.innerText = internalLink.text;
      newLink.href = '#';
      internalLink.parentNode.replaceChild(newLink, internalLink);
    });
  };

  addIdToh1 = (html) => {
    const container = document.createElement('div');
    container.innerHTML = html;

    Array.from(container.querySelectorAll('h1')).forEach((h1) => {
      const newH1 = document.createElement('h1');
      newH1.innerText = h1.innerText;
      newH1.id = h1.innerText.toLowerCase().replace(/[():]/g, '').replace(/ /g, '-');
      newH1.className = 'anchor';
      container.replaceChild(newH1, h1);
    });

    return container.innerHTML;
  };

  internalLink = (e, path) => {
    e.preventDefault();
    browserHistory.push(path);
    return false;
  };

  grabTopicFromApi(slug) {
    this.setState({
      doSpin: true
    });

    portalHttp.sendGet(`DP_URL/portal/api/guides/topic/${slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const topic = response.data.data;
      topic.content = this.addIdToh1(topic.content);
      this.setState({
        doSpin: false,
        topic,
      });
    });
  }

  selectGuide = (guide) => {
    portalHttp.sendGet(`DP_URL/portal/api/guides/topics/${guide.slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const topics = response.data.data;
      this.setState({
        topics,
      });
      const topic = Object.values(topics).pop();
      browserHistory.push(`/${this.props.params.locale}/guides/${guide.slug}/${topic.slug}`);
    });
  };

  render() {
    const { topics } = this.state;
    const { locale, splat } = this.props.params;
    const guideSlug = this.getGuideSlug(splat);
    return (
      <div>
        <div className="topic-list">
          <GuideSelector guideSlug={this.state.guideSlug} selectGuide={this.selectGuide} />
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
            <div dangerouslySetInnerHTML={{ __html: this.state.topic.content }} />
          </div>
        </div>
        <div className="comment-column" />
        <div className="content-summary">
          <TopicSummary content={this.state.topic.content} />
        </div>
      </div>
    );
  }
}

export default ViewTopic;
