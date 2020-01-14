import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import moment from 'moment';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import browserHistory from 'react-router/lib/browserHistory';
import { Link, Element, Events, scroller } from 'react-scroll';
import { TopicList, Topic, GuideSelector, CodeBlock } from '../index';

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
    const topicList = JSON.parse(window.topicList);
    this.state = {
      fixed:     false,
      doSpin:    false,
      flashes:   [],
      topics:    { [topic.id]: topic },
      guideSlug: this.getGuideSlug(this.props.params.splat),
      topicList,
    };
    this.contentChanged = false;
    this.ticking = false;
    this.targetSlug = props.params.slug;
    window.onload = this.defineSizes;
    moment.locale(window.DESKPRO_LOCALE);
  }

  componentDidMount() {
    this.changeInternalLinks();
    this.addCodeBlocksCopy();
    window.addEventListener('scroll', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          this.handleScroll();
          this.ticking = false;
        });
      }
      this.ticking = true;
    });
    this.grabAllTopicsFromApi();
    window.addEventListener('resize', this.defineSizes);
    this.defineSizes();
    Events.scrollEvent.register('begin', () => {
      this.scrolling = true;
    });
    Events.scrollEvent.register('end', () => {
      this.scrolling = false;
    });
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
      this.defineSizes();
    }
  }

  componentWillUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    window.addEventListener('resize', this.defineSizes);
  }

  getGuideSlug = splat => splat.split('/')[0];

  handleScroll = () => {
    if (this.state.fixed !== this.elements.guidesMain.getBoundingClientRect().top < 0) {
      this.setState({
        fixed: this.elements.guidesMain.getBoundingClientRect().top < 0,
      });
    }
  };

  defineSizes = () => {
    if (!this.elements) {
      this.elements = {
        guidesMain:   window.document.getElementById('react_helpcenter_bundle'),
        search:       window.document.getElementsByClassName('dp-po-guides-search')[0],
        articleRight: window.document.getElementsByClassName('dp-po-guides-block-article-right')[0],
      };
    }
    if (!this.sizes) {
      this.sizes = {
        topMargin:    this.elements.guidesMain && this.elements.guidesMain.getBoundingClientRect().top - document.documentElement.scrollTop,
        searchWidth:  this.elements.search && this.elements.search.getBoundingClientRect().width,
        articleWidth: this.elements.articleRight && this.elements.articleRight.getBoundingClientRect().width,
      };
    }
  };

  changeInternalLinks = () => {
    const links = document.querySelectorAll('a.internal_link.topic');
    Array.prototype.forEach.call(links, (internalLink) => {
      let target = internalLink.pathname;

      const guideSlug = target.replace(/^(\/[^/]+)?\/guides\//, '').replace(/\/.*/, '');
      let newLink;
      if (guideSlug !== this.state.guideSlug) {
        newLink = document.createElement('a');
        newLink.className = 'internal_link topic';
        newLink.onclick = e => this.internalLink(e, target);
        newLink.href = '#';
        if (internalLink.hash) {
          target += internalLink.hash;
        }
        newLink.innerText = internalLink.text;
        internalLink.parentNode.replaceChild(newLink, internalLink);
      } else {
        newLink = document.createElement('span');
        const topicSlug = target.replace(/^.+\/([^/]+)$/, '$1');
        const link = (
          <Link
            to={`topic_${topicSlug}`}
            href={target}
            offset={-129}
            isDynamic
          >
            {internalLink.text}
          </Link>
        );
        ReactDOM.render(link, newLink, () => {
          internalLink.parentNode.replaceChild(newLink, internalLink);
        });
      }
    });
  };

  addCodeBlocksCopy = () => {
    const blocks = document.querySelectorAll('pre code');
    Array.prototype.forEach.call(blocks, (block) => {
      this.addCodeBlockCopy(block);
    });
  };

  addCodeBlockCopy = (block) => {
    if (block.innerText) {
      const codeBlock = <CodeBlock text={block.innerText} html={block.innerHTML} />;
      ReactDOM.render(codeBlock, block);
    } else {
      setTimeout(() => { this.addCodeBlockCopy(block); }, 500);
    }
  };

  addReactImageLazyload = () => {
    const guideBlock = document.getElementsByClassName('dp-po-guides-block')[0];
    const images = guideBlock.querySelectorAll('.dp-po-guides-block-content img');
    if ('IntersectionObserver' in window) {
      const lazyImageObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const lazyImage = entry.target;
            lazyImage.src = lazyImage.dataset.src;
            // lazyImage.srcset = lazyImage.dataset.srcset;
            lazyImage.classList.remove('lazy');
            lazyImageObserver.unobserve(lazyImage);
          }
        });
      });

      images.forEach((lazyImage) => {
        if (!lazyImage.attributes.width && (lazyImage.dataset.width || lazyImage.dataset.with)) {
          if (lazyImage.dataset.with) {
            lazyImage.setAttribute('width', lazyImage.dataset.with);
          } else if (lazyImage.dataset.width) {
            lazyImage.setAttribute('width', lazyImage.dataset.width);
          }
        }
        lazyImageObserver.observe(lazyImage);
      });
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
    const guideSlug = path.replace(/^(\/[^/]+)?\/guides\//, '').replace(/\/.*/, '');
    if (guideSlug !== this.state.guideSlug) {
      portalHttp.sendGet(`DP_URL/portal/api/guides/topics/${guideSlug}`).then((response) => {
        if (response.isError()) {
          return;
        }

        const topicList = response.data.data;
        this.setState({
          topicList,
        });
      });
    }
    browserHistory.push(path);
    return false;
  };

  grabTopicFromApi = (slug) => {
    if (this.scrolling) {
      return;
    }
    const { topics, topicList } = this.state;
    const item = topicList.find(t => t.slug === slug);
    if (item) {
      if (typeof topics[item.id] !== 'undefined') {
        return;
      }
    }

    topics[item.id] = {};

    portalHttp.sendGet(`DP_URL/portal/api/guides/topic/${slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const topic = response.data.data;
      topic.content = this.addIdToh1(topic.content);
      topics[topic.id] = topic;
      this.setState({
        topics,
        flashes: [],
      });
      this.changeInternalLinks();
      this.addCodeBlocksCopy();
      this.addReactImageLazyload();
    });
  };

  grabAllTopicsFromApi = () => {
    portalHttp.sendGet(`DP_URL/portal/api/guides/all/${this.state.guideSlug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const topics = {};
      const res = response.data.data;
      res.forEach((topic) => {
        topic.content = this.addIdToh1(topic.content);
        topics[topic.id] = topic;
      });
      this.setState({
        topics,
        flashes: []
      });
      this.changeInternalLinks();
      this.addCodeBlocksCopy();
      this.addReactImageLazyload();
      setTimeout(() => {
        this.setState({
          loaded: true,
        });
        scroller.scrollTo(`topic_${this.targetSlug}`, {
          isDynamic: true,
          offset:    -129,
        });
      }, 500);
    });
  };

  selectGuide = (guide) => {
    portalHttp.sendGet(`DP_URL/portal/api/guides/topics/${guide.slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const topicList = response.data.data;
      this.setState({
        topicList,
        guideSlug: guide.slug,
      });
      const topic = Object.values(topicList).sort(
        (a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10)
      ).shift();

      this.grabAllTopicsFromApi();
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
      window.scrollTo(0, 0);
    });
  };

  renderTopics() {
    const { topicList, topics, guideSlug, topicSlug, loaded } = this.state;
    const result = [];

    topicList
      .forEach((topic) => {
        if (parseInt(topic.no_content, 10) === 1 || topic.depth === 0) {
          result.push(<Element name={`topic_${topic.slug}`} key={topic.id} />);
        } else {
          result.push(
            <Element
              key={topic.id}
              name={`topic_${topic.slug}`}
            >
              <Topic
                topic={topic}
                guideSlug={guideSlug}
                topicSlug={topicSlug}
                data={topics[topic.id]}
                sizes={this.sizes}
                loaded={loaded}
              />
            </Element>
          );
        }
      });
    return result;
  }

  render() {
    const { topicList, fixed, loaded } = this.state;
    const { splat, slug: topicSlug } = this.props.params;
    const guideSlug = this.getGuideSlug(splat);

    return (
      <div className={classNames({ fixed })}>
        <GuideSelector
          guideSlug={this.state.guideSlug}
          selectGuide={this.selectGuide}
          fixed={fixed}
        />
        <div className="dp-po-guides-section">
          <div className="dp-po-guides-wrap">
            <div className="container-fluid">
              <div className="row">
                <div className="col-sm-3">
                  <TopicList
                    topics={topicList}
                    guideSlug={guideSlug}
                    topicSlug={topicSlug}
                    grabTopicFromApi={this.grabTopicFromApi}
                    sizes={this.sizes}
                  />
                </div>
                <div className="col-sm-9">
                  { loaded ||
                    <div className={classNames({ 'dp-po-guides-loading': !loaded })}>
                      <i className="dp-icon fa-3x far fa-spinner fa-pulse" />
                    </div>
                  }
                  <div className="dp-po-guides-block">
                    {this.renderTopics()}
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
