import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import moment from 'moment';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import browserHistory from 'react-router/lib/browserHistory';
import { TopicList, TopicSummary, GuideSelector, CodeBlock } from '../index';

class ViewTopic extends React.Component {
  static propTypes = {
    params: PropTypes.object
  };

  constructor(props) {
    super(props);
    let topic = {};
    if (window.topic) {
      topic = JSON.parse(window.topic);
    }
    topic.content = this.addIdToh1(topic.content);
    const topics = JSON.parse(window.topicList);
    this.state = {
      fixed:     false,
      doSpin:    false,
      flashes:   [],
      topic,
      guideSlug: this.getGuideSlug(this.props.params.splat),
      topics
    };
    this.contentChanged = false;
    this.ticking = false;
    window.onload = this.defineSizes;
    moment.locale(window.DESKPRO_LOCALE);
  }

  componentDidMount() {
    this.addCodeBlocksCopy();
    window.addEventListener('scroll', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          this.ticking = false;
        });
      }
      this.ticking = true;
    });
    window.addEventListener('resize', this.defineSizes);
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
    }
  }

  componentWillUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    window.addEventListener('resize', this.defineSizes);
  }

  getGuideSlug = splat => splat.split('/')[0];

  addCodeBlocksCopy = () => {
    const blocks = document.querySelectorAll('pre code');
    Array.prototype.forEach.call(blocks, (block) => {
      this.addCodeBlockCopy(block);
    });
  };

  addCodeBlockCopy = (block) => {
    if (block.innerText) {
      const anchor = <CodeBlock text={block.innerText} html={block.innerHTML} />;
      ReactDOM.render(anchor, block);
    } else {
      setTimeout(() => { this.addCodeBlockCopy(block); }, 500);
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
        doSpin:  false,
        topic,
        flashes: [],
      });
      this.addCodeBlocksCopy();
      window.scrollTo(0, 0);
      setTimeout(this.defineSizes, 100);
      setTimeout(this.hashLinkScroll, 100);
    });
  }

  hashLinkScroll = () => {
    const { hash } = window.location;
    if (hash !== '') {
      // Push onto callback queue so it runs after the DOM is updated,
      // this is required when navigating from a different page so that
      // the element is rendered on the page before trying to getElementById.
      setTimeout(() => {
        const id = hash.replace('#', '');
        const element = document.getElementById(id);
        if (element) element.scrollIntoView();
      }, 0);
    }
  };

  selectGuide = (guide) => {
    portalHttp.sendGet(`DP_URL/portal/api/guides/topics/${guide.slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const topics = response.data.data;
      this.setState({
        topics,
      });
      const topic = Object.values(topics).sort(
        (a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10)
      ).shift();

      let baseUrl = window.DESKPRO_BASE_URL;
      if (baseUrl) {
        baseUrl = baseUrl.replace(/\/+$/, '');
      }

      if (Object.values(topic.children).length) {
        const child = Object.values(topic.children).sort(
          (a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10)
        ).shift();
        browserHistory.push(`${baseUrl}/guides/${guide.slug}/${topic.slug}/${child.slug}`);
      } else {
        browserHistory.push(`${baseUrl}/guides/${guide.slug}/${topic.slug}`);
      }
    });
  };

  render() {
    const { topics, topic } = this.state;
    const { splat } = this.props.params;
    const guideSlug = this.getGuideSlug(splat);
    const { fixed } = this.state;
    const agentBarHeight = this.sizes ? this.agentBarHeight : 0;

    return (
      <div>
        <GuideSelector guideSlug={this.state.guideSlug} selectGuide={this.selectGuide} />
        <div className="dp-po-guides-section">
          <div className="dp-po-guides-wrap">
            <div className="container-fluid">
              <div className="row">
                <div className="col-sm-3">
                  <TopicList topics={topics} guideSlug={guideSlug} />
                </div>
                <div className="col-sm-9">
                  <div className="dp-po-guides-block">
                    <div className="dp-po-guides-block-article" id={topic.slug}>
                      <div className="row">
                        <div className="col-sm-9">
                          <div className="dp-po-guides-block-article-left">
                            <div className="dp-po-guides-block-header">
                              <div>
                                <h2 className="dp-po-guides-block-title dp-po-clipboard">{topic.title} <a
                                  className="dp-po-clipboard-link" data-toggle="tooltip"
                                  data-placement="top" title="Copy to Clipboard"
                                ><i
                                  className="dp-po-icon far fa-anchor"
                                /></a></h2>
                                <a href="" className="dp-po-guides-block-chapter"><i
                                  className="dp-po-icon fal fa-angle-right"
                                />
                                  LDAP</a>
                              </div>
                              <div className="dp-po-guides-block-extra">
                                <ul className="dp-po-guides-block-extra-list">
                                  <li className="dp-po-guides-block-extra-item">
                                    <a href="" className="dp-po-guides-block-extra-link"><i
                                      className="dp-po-icon fal fa-print"
                                    /></a>
                                  </li>
                                  <li className="dp-po-guides-block-extra-item">
                                    <a href="" className="dp-po-guides-block-extra-link">
                                      <i className="dp-po-icon fal fa-file-pdf" />
                                    </a>
                                  </li>
                                </ul>
                              </div>
                            </div>
                            <div
                              className="dp-po-post-content dp-po-guides-block-content"
                              dangerouslySetInnerHTML={{ __html: topic.content }}
                            />
                          </div>
                        </div>
                        <div className="col-sm-3">
                          <div className="dp-po-guides-block-article-right">
                            <TopicSummary content={topic.content} fixed={fixed} agentBarHeight={agentBarHeight} />
                            <div className="dp-po-guides-meta">
                              <p>Published: <strong>{moment(topic.date_published).format('DD/MM/YYYY')}</strong></p>
                              <p>Last updated: <strong>{moment(topic.date_updated).format('DD/MM/YYYY')}</strong></p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default ViewTopic;
