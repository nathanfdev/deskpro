import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import moment from 'moment';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import browserHistory from 'react-router/lib/browserHistory';
import { TopicList, TopicSummary, GuideSelector, Anchor } from '../index';

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
      fixed:     false,
      doSpin:    false,
      topic,
      guideSlug: this.getGuideSlug(this.props.params.splat),
      topics
    };
    this.contentChanged = false;
    document.addEventListener('DOMContentLoaded', () => {
      this.defineSizes();
    });
  }

  componentDidMount() {
    this.changeInternalLinks();
    this.addAnchorLinks();
    window.addEventListener('scroll', this.handleScroll);
    window.addEventListener('resize', this.defineSizes);
    this.offsetTop = this.topicList.offsetTop;
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.params.slug !== this.props.params.slug) {
      this.grabTopicFromApi(nextProps.params.slug);
      this.contentChanged = true;
    }
    const nextGuideSlug = this.getGuideSlug(nextProps.params.splat);
    if (nextGuideSlug !== this.getGuideSlug(this.props.params.splat)) {
      this.setState({
        guideSlug: nextGuideSlug
      });
    }
  }

  componentDidUpdate() {
    if (this.contentChanged) {
      this.contentChanged = false;
      this.changeInternalLinks();
      this.addAnchorLinks();
      this.defineSizes();
    }
  }

  componentWillUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    window.addEventListener('resize', this.defineSizes);
  }

  defineSizes = () => {
    if (!this.sizes) {
      this.sizes = {
        agentBarHeight:    0,
        dpPageBodyPadding: 0,
        worldPadding:      0,
        pageTopSection:    0,
        searchSection:     0,
        welcomeNews:       0,
        pageBottomSection: 0
      };
      const agentBar = window.document.getElementById('agent-bar');
      this.sizes.agentBarHeight = 0;
      if (agentBar) {
        this.sizes.agentBarHeight = agentBar.offsetHeight;
      }
      const dpPageBody = window.document.getElementsByClassName('dp-page-body')[0];
      if (!dpPageBody) {
        return false;
      }
      let style = window.getComputedStyle(dpPageBody, null);
      this.sizes.dpPageBodyPadding = parseInt(style.getPropertyValue('padding-top'), 10)
        + parseInt(style.getPropertyValue('padding-bottom'), 10);
      const world = dpPageBody.getElementsByClassName('world')[0];
      if (!world) {
        return false;
      }
      style = window.getComputedStyle(world, null);
      this.sizes.worldPadding = parseInt(style.getPropertyValue('padding-top'), 10)
        + parseInt(style.getPropertyValue('padding-bottom'), 10);
      const pageTopSection = world.getElementsByClassName('page-top-section')[0];
      if (pageTopSection) {
        this.sizes.pageTopSection = pageTopSection.scrollHeight;
      }
      const searchSection = world.getElementsByClassName('search-section')[0];
      if (searchSection) {
        this.sizes.searchSection = searchSection.scrollHeight;
      }
      const welcomeNews = world.getElementsByClassName('welcome-news')[0];
      if (welcomeNews) {
        style = window.getComputedStyle(welcomeNews.children[0], null);
        this.sizes.welcomeNews = welcomeNews.scrollHeight + parseInt(style.getPropertyValue('margin-top'), 10) + 5;
      }
      const pageBottomSection = world.getElementsByClassName('page-bottom-section')[0];
      if (pageBottomSection) {
        this.sizes.pageBottomSection = pageBottomSection.scrollHeight;
      }
    }
    this.sizes.topOffset = this.sizes.agentBarHeight
      + this.sizes.dpPageBodyPadding / 2
      + this.sizes.worldPadding / 2
      + this.sizes.pageTopSection
      + this.sizes.searchSection
      + this.sizes.welcomeNews;
    this.sizes.bottomOffset =  this.sizes.dpPageBodyPadding / 2
      + this.sizes.worldPadding / 2
      + this.sizes.pageBottomSection;
    this.sizes.topicListHeight = window.innerHeight
      - this.sizes.agentBarHeight
      - this.sizes.dpPageBodyPadding
      - this.sizes.worldPadding
      - this.sizes.pageTopSection
      - this.sizes.searchSection
      - this.sizes.welcomeNews
      - this.sizes.pageBottomSection;
    window.document.getElementsByClassName('topic-list')[0].style = `max-height: ${this.sizes.topicListHeight}px; top: ${this.sizes.agentBarHeight + 20}px`;
    window.document.getElementsByClassName('topic-summary')[0].style = `top: ${this.sizes.agentBarHeight + 20}px`;
  };

  handleScroll = (event) => {
    if (this.sizes) {
      const topOffset = this.offsetTop - 20 - this.sizes.agentBarHeight;
      let maxHeight;
      if (event.srcElement.body.scrollTop > topOffset) {
        if (!this.state.fixed) {
          this.setState({
            fixed: true,
          });
        }
        if (this.topic.scrollHeight > this.sizes.topicListHeight) {
          maxHeight = topOffset + this.sizes.topicListHeight;
        }
      } else {
        if (this.state.fixed) {
          this.setState({
            fixed: false,
          });
        }
        if (this.topic.scrollHeight > this.sizes.topicListHeight) {
          maxHeight = event.srcElement.body.scrollTop + this.sizes.topicListHeight;
        }
      }
      const bottomScroll = event.srcElement.body.scrollHeight - event.srcElement.body.scrollTop - window.innerHeight;
      if (bottomScroll < this.sizes.pageBottomSection) {
        maxHeight += bottomScroll;
      } else {
        maxHeight += this.sizes.pageBottomSection;
      }
      window.document.getElementsByClassName('topic-list')[0].style = `max-height: ${maxHeight}px; top: ${this.sizes.agentBarHeight + 20}px`;
    }
  };

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

  addAnchorLinks = () => {
    document.querySelectorAll('h1.anchor').forEach((h1) => {
      this.addAnchorLink(h1);
    });
  };

  addAnchorLink = (h1) => {
    if (h1.innerText) {
      const anchor = <Anchor anchor={h1.id} text={h1.innerText} />;
      ReactDOM.render(anchor, h1);
    } else {
      setTimeout(() => { this.addAnchorLink(h1); }, 500);
    }
  };

  addIdToh1 = (html) => {
    const container = document.createElement('div');
    container.innerHTML = html;

    Array.from(container.querySelectorAll('h1')).forEach((h1) => {
      const newH1 = document.createElement('h1');
      newH1.innerText = `${h1.innerText} `;
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
    const { fixed } = this.state;
    const agentBarHeight = this.sizes ? this.agentBarHeight : 0;
    return (
      <div>
        <div className={classNames('topic-list', { fixed })} ref={(c) => { this.topicList = c; }} >
          <GuideSelector guideSlug={this.state.guideSlug} selectGuide={this.selectGuide} />
          <hr />
          <TopicList topics={topics} locale={locale} guideSlug={guideSlug} />
        </div>
        <div className="topic" ref={(c) => { this.topic = c; }}>
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
        <div className="content-summary">
          <TopicSummary content={this.state.topic.content} fixed={fixed} agentBarHeight={agentBarHeight} />
        </div>
      </div>
    );
  }
}

export default ViewTopic;
