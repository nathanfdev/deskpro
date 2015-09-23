import React from 'react';

export default class Footer extends React.Component {
    render() {
        return (
            <footer>
                <form>
                    <input type="text" placeholder="Send a message" />
                    <a href="#" className="insert-emoticon"><span className="emoticon sprite sprite-emoticon-1"></span></a>
                    <input type="button" value="&#xf101;" />

                    <div className="emoticon-panel">
                        <div>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-1"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-2"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-3"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-4"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-5"></span></a>
                        </div>

                        <div>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-6"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-7"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-8"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-9"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-10"></span></a>
                        </div>

                        <div>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-11"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-12"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-13"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-14"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-15"></span></a>
                        </div>

                        <div>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-16"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-17"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-18"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-19"></span></a>
                            <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-20"></span></a>
                        </div>
                    </div>
                </form>
            </footer>
        );
    }
}