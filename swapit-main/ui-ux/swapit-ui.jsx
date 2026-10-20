import { useState, useEffect, useRef } from "react";

const G = "#2C6E49", G2 = "#40916C", O = "#E76F51", CR = "#F8F4EE", DK = "#1A1A2E";
const css = `
  @import url('https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap');
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{
    --g:#2C6E49;--g2:#40916C;--o:#E76F51;--cr:#F8F4EE;--dk:#1A1A2E;
    --gr:#6B7280;--bd:#E2DDD6;--white:#fff;
    --shadow:0 4px 24px rgba(0,0,0,.08);
    --shadow-lg:0 12px 48px rgba(0,0,0,.14);
  }
  body{font-family:'DM Sans',sans-serif;background:var(--cr);color:var(--dk);overflow-x:hidden}
  h1,h2,h3,h4,.syne{font-family:'Syne',sans-serif}
  input,textarea,select{font-family:'DM Sans',sans-serif}
  button{cursor:pointer}
  a{text-decoration:none}
  ::-webkit-scrollbar{width:5px;height:5px}
  ::-webkit-scrollbar-track{background:transparent}
  ::-webkit-scrollbar-thumb{background:rgba(44,110,73,.3);border-radius:99px}

  @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
  @keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
  @keyframes spin{to{transform:rotate(360deg)}}
  @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
  @keyframes slideIn{from{opacity:0;transform:translateX(-20px)}to{opacity:1;transform:translateX(0)}}
  @keyframes countUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

  .fade-up{animation:fadeUp .5s ease both}
  .card{background:#fff;border-radius:16px;box-shadow:var(--shadow);overflow:hidden}
  .pill{display:inline-flex;align-items:center;gap:5px;border-radius:99px;padding:4px 12px;font-size:12px;font-weight:500}
  .pill-g{background:#D1FAE5;color:#065F46}
  .pill-o{background:#FEE2D5;color:#9B2826}
  .pill-b{background:#DBEAFE;color:#1E40AF}
  .pill-y{background:#FEF3C7;color:#92400E}
  .badge-red{position:absolute;top:-4px;right:-4px;width:18px;height:18px;background:#EF4444;border-radius:50%;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff}
  .btn{border:none;border-radius:12px;font-family:'Syne',sans-serif;font-weight:700;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:8px}
  .btn:hover{transform:translateY(-2px)}
  .btn:active{transform:translateY(0)}
  .btn-green{background:var(--g);color:#fff;padding:11px 22px;font-size:14px}
  .btn-green:hover{background:var(--g2);box-shadow:0 4px 16px rgba(44,110,73,.35)}
  .btn-outline{background:transparent;border:1.5px solid var(--g);color:var(--g);padding:10px 22px;font-size:14px}
  .btn-orange{background:var(--o);color:#fff;padding:11px 22px;font-size:14px}
  .btn-ghost{background:rgba(0,0,0,.06);color:var(--dk);padding:10px 20px;font-size:13px}
  .btn-sm{padding:7px 16px;font-size:12.5px;border-radius:99px}
  .inp{width:100%;padding:11px 14px 11px 42px;border:1.5px solid var(--bd);border-radius:11px;font-size:13.5px;color:var(--dk);background:#fff;outline:none;transition:.2s}
  .inp:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(44,110,73,.1)}
  .inp-wrap{position:relative}
  .inp-ico{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1}
  .section-title{font-family:'Syne',sans-serif;font-size:24px;font-weight:800;color:var(--dk);margin-bottom:4px}
  .section-sub{font-size:14px;color:var(--gr);margin-bottom:24px}
`;

function ListingCard({ item, style = {} }) {
  const [saved, setSaved] = useState(false);
  const badges = { sale: ["pill-g", "For Sale"], swap: ["pill-o", "Swap"], auction: ["pill-b", "Auction"] };
  const [bc, bl] = badges[item.type] || badges.sale;
  return (
    <div className="card" style={{ transition: ".25s", cursor: "pointer", ...style }}
      onMouseEnter={e => { e.currentTarget.style.transform = "translateY(-5px)"; e.currentTarget.style.boxShadow = "0 16px 48px rgba(0,0,0,.13)"; }}
      onMouseLeave={e => { e.currentTarget.style.transform = ""; e.currentTarget.style.boxShadow = ""; }}>
      <div style={{ position: "relative", height: 160, background: item.bg || "#e9f5ee", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 52 }}>
        {item.emoji}
        <span className={`pill ${bc}`} style={{ position: "absolute", top: 10, left: 10 }}>{bl}</span>
        <button onClick={e => { e.stopPropagation(); setSaved(!saved); }}
          style={{ position: "absolute", top: 10, right: 10, width: 32, height: 32, borderRadius: "50%", background: "rgba(255,255,255,.9)", border: "none", fontSize: 15, display: "flex", alignItems: "center", justifyContent: "center", boxShadow: "0 2px 8px rgba(0,0,0,.1)" }}>
          {saved ? "❤️" : "🤍"}
        </button>
      </div>
      <div style={{ padding: "14px 16px 16px" }}>
        <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14, marginBottom: 6, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{item.title}</div>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 10 }}>
          <span className="pill pill-g" style={{ fontSize: 11 }}>{item.condition}</span>
          {item.type === "auction" ? (
            <div style={{ fontSize: 12, color: "#EF4444", fontWeight: 600 }}>⏱ {item.ends}</div>
          ) : null}
        </div>
        <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 18, color: item.type === "swap" ? O : G, marginBottom: 10 }}>
          {item.type === "swap" ? "Swap Only" : item.price}
        </div>
        <div style={{ display: "flex", alignItems: "center", gap: 6, fontSize: 12, color: "#6B7280" }}>
          <div style={{ width: 22, height: 22, borderRadius: "50%", background: G, color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 10, fontWeight: 700 }}>{item.seller[0]}</div>
          <span style={{ fontWeight: 500 }}>{item.seller}</span>
          <span style={{ marginLeft: "auto" }}>⭐ {item.rating}</span>
          <span>· {item.location}</span>
        </div>
      </div>
    </div>
  );
}

function Navbar({ onNav, active }) {
  const [search, setSearch] = useState("");
  const cats = ["All", "Electronics", "Clothing", "Books", "Furniture", "Sports", "Vehicles"];
  const [activeCat, setActiveCat] = useState("All");
  return (
    <nav style={{ background: "#fff", borderBottom: "1px solid #E2DDD6", position: "sticky", top: 0, zIndex: 200, boxShadow: "0 2px 12px rgba(0,0,0,.05)" }}>
      <div style={{ display: "flex", alignItems: "center", gap: 16, padding: "12px 32px", maxWidth: 1280, margin: "0 auto" }}>
        <button onClick={() => onNav("home")} style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 20, color: DK, background: "none", border: "none", display: "flex", alignItems: "center", gap: 8, cursor: "pointer" }}>
          <div style={{ width: 32, height: 32, background: G, borderRadius: 8, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 16 }}>🔄</div>
          SwapIt
        </button>
        <div style={{ flex: 1, maxWidth: 480, display: "flex", background: "#F3F4F6", borderRadius: 99, overflow: "hidden", border: "1.5px solid transparent", transition: ".2s" }}
          onFocus={e => e.currentTarget.style.borderColor = G} onBlur={e => e.currentTarget.style.borderColor = "transparent"}>
          <span style={{ padding: "0 14px", display: "flex", alignItems: "center", fontSize: 15 }}>🔍</span>
          <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search for items, categories..." style={{ flex: 1, border: "none", background: "transparent", padding: "10px 0", fontSize: 13.5, outline: "none" }} />
          <button style={{ padding: "8px 16px", background: G, color: "#fff", border: "none", margin: 3, borderRadius: 99, fontSize: 12, fontWeight: 600 }}>Search</button>
        </div>
        <div style={{ display: "flex", alignItems: "center", gap: 10, marginLeft: "auto" }}>
          <button onClick={() => onNav("create")} className="btn btn-outline btn-sm">+ Sell / Swap</button>
          <button style={{ position: "relative", background: "none", border: "none", fontSize: 20, padding: "4px 6px" }}>
            🔔<span className="badge-red">3</span>
          </button>
          <button style={{ position: "relative", background: "none", border: "none", fontSize: 20, padding: "4px 6px" }}>❤️</button>
          <button onClick={() => onNav("profile")} style={{ width: 36, height: 36, borderRadius: "50%", background: G, color: "#fff", border: "none", fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14 }}>A</button>
        </div>
      </div>
      <div style={{ borderTop: "1px solid #F3F4F6", overflowX: "auto" }}>
        <div style={{ display: "flex", gap: 6, padding: "8px 32px", maxWidth: 1280, margin: "0 auto" }}>
          {cats.map(c => (
            <button key={c} onClick={() => setActiveCat(c)}
              style={{ padding: "5px 16px", borderRadius: 99, border: "none", background: activeCat === c ? G : "transparent", color: activeCat === c ? "#fff" : "#6B7280", fontFamily: "'DM Sans',sans-serif", fontSize: 13, fontWeight: 500, whiteSpace: "nowrap", transition: ".2s" }}>
              {c}
            </button>
          ))}
        </div>
      </div>
    </nav>
  );
}

function SideNav({ onNav, active }) {
  const items = [
    { id: "home", ico: "🏠", label: "Home" },
    { id: "browse", ico: "🔍", label: "Browse" },
    { id: "create", ico: "➕", label: "Create Listing" },
    { id: "messages", ico: "💬", label: "Messages", badge: 3 },
    { id: "swaps", ico: "🔄", label: "Swap Offers", badge: 2 },
    { id: "auctions", ico: "🔨", label: "Auctions" },
    { id: "wishlist", ico: "❤️", label: "Wishlist" },
    { id: "profile", ico: "👤", label: "My Profile" },
    { id: "orders", ico: "📦", label: "My Orders" },
    { id: "bids", ico: "📊", label: "My Bids" },
    { id: "admin", ico: "⚙️", label: "Admin Panel" },
  ];
  return (
    <div style={{ width: 220, background: DK, minHeight: "100vh", padding: "24px 12px", display: "flex", flexDirection: "column", gap: 4 }}>
      <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 18, color: "#fff", display: "flex", alignItems: "center", gap: 10, padding: "0 12px 20px", borderBottom: "1px solid rgba(255,255,255,.08)", marginBottom: 8 }}>
        <div style={{ width: 30, height: 30, background: G, borderRadius: 8, display: "flex", alignItems: "center", justifyContent: "center" }}>🔄</div>
        SwapIt
      </div>
      {items.map(item => (
        <button key={item.id} onClick={() => onNav(item.id)}
          style={{ display: "flex", alignItems: "center", gap: 10, padding: "10px 14px", borderRadius: 10, border: "none", background: active === item.id ? "rgba(44,110,73,.35)" : "transparent", color: active === item.id ? "#fff" : "rgba(255,255,255,.55)", fontSize: 13.5, fontWeight: active === item.id ? 600 : 400, textAlign: "left", transition: ".15s", position: "relative" }}>
          <span style={{ fontSize: 17 }}>{item.ico}</span>
          {item.label}
          {item.badge ? <span style={{ marginLeft: "auto", background: "#EF4444", color: "#fff", borderRadius: 99, padding: "1px 7px", fontSize: 10, fontWeight: 700 }}>{item.badge}</span> : null}
        </button>
      ))}
    </div>
  );
}

const LISTINGS = [
  { id: 1, title: "iPhone 14 Pro 256GB Space Black", type: "sale", price: "350 JD", condition: "Like New", emoji: "📱", bg: "#e8f0fe", seller: "Ahmad K", rating: 4.9, location: "Amman" },
  { id: 2, title: "Sony WH-1000XM5 Headphones", type: "auction", price: "Starting 85 JD", condition: "Good", emoji: "🎧", bg: "#fce8e8", seller: "Sara M", rating: 4.7, location: "Irbid", ends: "2h 14m" },
  { id: 3, title: "Men's Leather Jacket XL — Premium", type: "swap", price: null, condition: "Good", emoji: "🧥", bg: "#fff3e0", seller: "Omar T", rating: 4.5, location: "Zarqa" },
  { id: 4, title: "Canon EOS R50 Camera Kit", type: "sale", price: "480 JD", condition: "New", emoji: "📷", bg: "#e8fce8", seller: "Lina B", rating: 5.0, location: "Amman" },
  { id: 5, title: "MacBook Air M2 13\" 8GB/256GB", type: "sale", price: "720 JD", condition: "Like New", emoji: "💻", bg: "#f0e8ff", seller: "Khalid R", rating: 4.8, location: "Amman" },
  { id: 6, title: "Vintage Rolex Watch", type: "auction", price: "Starting 200 JD", condition: "Good", emoji: "⌚", bg: "#fef3e8", seller: "Nour A", rating: 4.6, location: "Aqaba", ends: "5h 33m" },
  { id: 7, title: "PlayStation 5 + 2 Controllers", type: "sale", price: "420 JD", condition: "Like New", emoji: "🎮", bg: "#e8f4fe", seller: "Yousef H", rating: 4.9, location: "Irbid" },
  { id: 8, title: "Road Bike Trek FX3 Disc 2022", type: "swap", price: null, condition: "Good", emoji: "🚴", bg: "#e8ffe8", seller: "Dina F", rating: 4.4, location: "Amman" },
];

function HomePage({ onNav }) {
  const [tab, setTab] = useState("featured");
  const stats = [
    { n: "12,400+", l: "Active Listings", c: "#B7E4C7" },
    { n: "4.8★", l: "Avg. Rating", c: O },
    { n: "850+", l: "Swaps Done", c: "#93C5FD" },
    { n: "200+", l: "Live Auctions", c: "#FCD34D" },
  ];
  const categories = [
    { name: "Electronics", emoji: "📱", count: 1240, color: "#EEF2FF" },
    { name: "Clothing", emoji: "👕", count: 876, color: "#FFF7ED" },
    { name: "Books", emoji: "📚", count: 543, color: "#F0FDF4" },
    { name: "Furniture", emoji: "🛋️", count: 321, color: "#FFF1F2" },
    { name: "Vehicles", emoji: "🚗", count: 289, color: "#F0F9FF" },
    { name: "Sports", emoji: "⚽", count: 412, color: "#FFFBEB" },
    { name: "Collectibles", emoji: "🏺", count: 198, color: "#FDF4FF" },
    { name: "Kids & Toys", emoji: "🧸", count: 334, color: "#F0FDF4" },
  ];
  return (
    <div>
      {}
      <div style={{ background: DK, padding: "64px 40px 80px", position: "relative", overflow: "hidden" }}>
        <div style={{ position: "absolute", width: 600, height: 600, borderRadius: "50%", background: "radial-gradient(circle,rgba(44,110,73,.5),transparent 65%)", top: -200, right: -100, pointerEvents: "none" }} />
        <div style={{ position: "absolute", width: 350, height: 350, borderRadius: "50%", background: "radial-gradient(circle,rgba(231,111,81,.25),transparent)", bottom: -100, left: 250, pointerEvents: "none" }} />
        <div style={{ maxWidth: 1200, margin: "0 auto", display: "flex", alignItems: "center", gap: 60, position: "relative", zIndex: 2 }}>
          <div style={{ flex: 1, animation: "fadeUp .6s ease both" }}>
            <div style={{ display: "inline-flex", alignItems: "center", gap: 8, background: "rgba(255,255,255,.08)", border: "1px solid rgba(255,255,255,.15)", borderRadius: 99, padding: "6px 14px", fontSize: 12.5, color: "rgba(255,255,255,.7)", marginBottom: 22 }}>
              <span style={{ width: 8, height: 8, background: "#EF4444", borderRadius: "50%", animation: "blink 1.2s infinite" }} />
              🔴 Live Auction ending in 2h 14m
            </div>
            <h1 style={{ fontSize: 54, fontWeight: 800, lineHeight: 1.08, marginBottom: 18, color: "#fff" }}>
              <span style={{ color: "#B7E4C7" }}>Buy.</span>{" "}
              <span>Sell.</span>{" "}
              <span style={{ color: O }}>Swap.</span>{" "}
              <span>Bid.</span>
            </h1>
            <p style={{ color: "rgba(255,255,255,.55)", fontSize: 17, lineHeight: 1.7, marginBottom: 32, maxWidth: 460 }}>
              Jordan's smartest marketplace. Trade fairly, swap freely, and bid on what you love — all in one place.
            </p>
            <div style={{ display: "flex", gap: 14 }}>
              <button onClick={() => onNav("browse")} className="btn btn-green" style={{ fontSize: 15, padding: "14px 28px" }}>Browse Listings</button>
              <button onClick={() => onNav("create")} className="btn" style={{ fontSize: 15, padding: "14px 28px", background: "rgba(255,255,255,.1)", color: "#fff", border: "1.5px solid rgba(255,255,255,.25)" }}>Start Selling →</button>
            </div>
            <div style={{ display: "flex", gap: 32, marginTop: 36 }}>
              {stats.map(s => (
                <div key={s.l}>
                  <div style={{ fontFamily: "'Syne',sans-serif", fontSize: 22, fontWeight: 800, color: s.c }}>{s.n}</div>
                  <div style={{ fontSize: 12, color: "rgba(255,255,255,.4)" }}>{s.l}</div>
                </div>
              ))}
            </div>
          </div>
          <div style={{ flex: "0 0 380px", display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10, animation: "fadeUp .7s .1s ease both" }}>
            {LISTINGS.slice(0, 4).map((item, i) => (
              <div key={item.id} style={{ background: "rgba(255,255,255,.07)", border: "1px solid rgba(255,255,255,.1)", borderRadius: 14, padding: 16, backdropFilter: "blur(6px)", gridColumn: i === 0 ? "1 / -1" : undefined }}>
                <div style={{ fontSize: 28, marginBottom: 8 }}>{item.emoji}</div>
                <div style={{ fontSize: 12, color: "#fff", fontWeight: 500, marginBottom: 4, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{item.title}</div>
                <div style={{ fontFamily: "'Syne',sans-serif", fontSize: 14, color: "#B7E4C7", fontWeight: 700 }}>{item.type === "swap" ? "Swap Only" : item.price}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {}
      <div style={{ maxWidth: 1200, margin: "0 auto", padding: "56px 40px" }}>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 28 }}>
          <div>
            <h2 className="section-title">Browse Categories</h2>
            <p className="section-sub">Find what you're looking for</p>
          </div>
          <button onClick={() => onNav("browse")} style={{ color: G, background: "none", border: "none", fontWeight: 600, fontSize: 14 }}>View All →</button>
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 14 }}>
          {categories.map(cat => (
            <div key={cat.name} onClick={() => onNav("browse")} style={{ background: "#fff", border: "1.5px solid #E2DDD6", borderRadius: 16, padding: "24px 20px", textAlign: "center", cursor: "pointer", transition: ".25s" }}
              onMouseEnter={e => { e.currentTarget.style.transform = "translateY(-4px)"; e.currentTarget.style.boxShadow = "0 12px 32px rgba(0,0,0,.09)"; e.currentTarget.style.borderColor = G; }}
              onMouseLeave={e => { e.currentTarget.style.transform = ""; e.currentTarget.style.boxShadow = ""; e.currentTarget.style.borderColor = "#E2DDD6"; }}>
              <div style={{ width: 56, height: 56, borderRadius: 14, background: cat.color, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 26, margin: "0 auto 12px" }}>{cat.emoji}</div>
              <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14, marginBottom: 4 }}>{cat.name}</div>
              <div style={{ fontSize: 12, color: "#6B7280" }}>{cat.count.toLocaleString()} listings</div>
            </div>
          ))}
        </div>
      </div>

      {}
      <div style={{ background: "#fff", padding: "56px 0" }}>
        <div style={{ maxWidth: 1200, margin: "0 auto", padding: "0 40px" }}>
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 28 }}>
            <div>
              <h2 className="section-title">Featured Listings</h2>
              <p className="section-sub">Hand-picked quality items</p>
            </div>
            <div style={{ display: "flex", gap: 8 }}>
              {["featured", "recent", "auctions"].map(t => (
                <button key={t} onClick={() => setTab(t)} style={{ padding: "6px 18px", borderRadius: 99, border: `1.5px solid ${tab === t ? G : "#E2DDD6"}`, background: tab === t ? G : "transparent", color: tab === t ? "#fff" : "#6B7280", fontSize: 13, fontWeight: 500, transition: ".15s" }}>
                  {t.charAt(0).toUpperCase() + t.slice(1)}
                </button>
              ))}
            </div>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 20 }}>
            {LISTINGS.map(item => <ListingCard key={item.id} item={item} />)}
          </div>
        </div>
      </div>

      {}
      <div style={{ maxWidth: 1200, margin: "0 auto", padding: "56px 40px" }}>
        <h2 className="section-title" style={{ textAlign: "center" }}>How SwapIt Works</h2>
        <p className="section-sub" style={{ textAlign: "center" }}>Get started in minutes</p>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 28, marginTop: 36 }}>
          {[
            { n: "01", ico: "📸", title: "Post Your Item", desc: "Take photos, set your price or swap preferences, and publish in seconds." },
            { n: "02", ico: "🤝", title: "Connect & Negotiate", desc: "Chat with buyers, review swap offers, or let bidders compete." },
            { n: "03", ico: "✅", title: "Deal & Exchange", desc: "Confirm the deal, meet safely, and leave a review afterwards." },
          ].map(s => (
            <div key={s.n} style={{ background: "#fff", borderRadius: 20, padding: "32px 28px", textAlign: "center", border: "1px solid #E2DDD6" }}>
              <div style={{ width: 64, height: 64, borderRadius: 18, background: "#D1FAE5", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 28, margin: "0 auto 16px" }}>{s.ico}</div>
              <div style={{ fontFamily: "'Syne',sans-serif", fontSize: 11, fontWeight: 700, color: G, letterSpacing: 2, marginBottom: 8 }}>STEP {s.n}</div>
              <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 18, marginBottom: 10 }}>{s.title}</h3>
              <p style={{ color: "#6B7280", fontSize: 14, lineHeight: 1.65 }}>{s.desc}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

function BrowsePage() {
  const [sort, setSort] = useState("newest");
  const [filters, setFilters] = useState({ type: "all", condition: "all", minPrice: "", maxPrice: "" });
  const [view, setView] = useState("grid");
  const [page, setPage] = useState(1);
  const activeFilters = Object.entries(filters).filter(([k, v]) => v && v !== "all" && v !== "");

  return (
    <div style={{ display: "flex", maxWidth: 1280, margin: "0 auto", padding: "28px 32px", gap: 28 }}>
      {}
      <div style={{ width: 260, flexShrink: 0 }}>
        <div className="card" style={{ padding: "20px" }}>
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 20 }}>
            <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16 }}>Filters</h3>
            {activeFilters.length > 0 && <button onClick={() => setFilters({ type: "all", condition: "all", minPrice: "", maxPrice: "" })} style={{ color: G, background: "none", border: "none", fontSize: 12, fontWeight: 600 }}>Clear all</button>}
          </div>

          {}
          {activeFilters.length > 0 && (
            <div style={{ display: "flex", flexWrap: "wrap", gap: 6, marginBottom: 16 }}>
              {activeFilters.map(([k, v]) => (
                <span key={k} style={{ display: "inline-flex", alignItems: "center", gap: 5, background: "#D1FAE5", color: "#065F46", borderRadius: 99, padding: "3px 10px", fontSize: 12 }}>
                  {v} <button onClick={() => setFilters(f => ({ ...f, [k]: k === "type" || k === "condition" ? "all" : "" }))} style={{ background: "none", border: "none", cursor: "pointer", fontSize: 12, padding: 0, color: "#065F46" }}>×</button>
                </span>
              ))}
            </div>
          )}

          {}
          <div style={{ borderTop: "1px solid #F3F4F6", paddingTop: 16, marginBottom: 16 }}>
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 13, marginBottom: 12 }}>Listing Type</div>
            <div style={{ display: "flex", gap: 6 }}>
              {["all", "sale", "swap", "auction"].map(t => (
                <button key={t} onClick={() => setFilters(f => ({ ...f, type: t }))}
                  style={{ flex: 1, padding: "6px 4px", borderRadius: 8, border: `1.5px solid ${filters.type === t ? G : "#E2DDD6"}`, background: filters.type === t ? G : "transparent", color: filters.type === t ? "#fff" : "#6B7280", fontSize: 11, fontWeight: 600, transition: ".15s" }}>
                  {t === "all" ? "All" : t.charAt(0).toUpperCase() + t.slice(1)}
                </button>
              ))}
            </div>
          </div>

          {}
          <div style={{ borderTop: "1px solid #F3F4F6", paddingTop: 16, marginBottom: 16 }}>
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 13, marginBottom: 12 }}>Price Range (JD)</div>
            <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
              <input value={filters.minPrice} onChange={e => setFilters(f => ({ ...f, minPrice: e.target.value }))} placeholder="Min" style={{ flex: 1, padding: "8px 10px", border: "1.5px solid #E2DDD6", borderRadius: 8, fontSize: 13, outline: "none" }} />
              <span style={{ color: "#aaa" }}>—</span>
              <input value={filters.maxPrice} onChange={e => setFilters(f => ({ ...f, maxPrice: e.target.value }))} placeholder="Max" style={{ flex: 1, padding: "8px 10px", border: "1.5px solid #E2DDD6", borderRadius: 8, fontSize: 13, outline: "none" }} />
            </div>
          </div>

          {}
          <div style={{ borderTop: "1px solid #F3F4F6", paddingTop: 16, marginBottom: 20 }}>
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 13, marginBottom: 12 }}>Condition</div>
            {["all", "New", "Like New", "Good", "Fair"].map(c => (
              <label key={c} style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 10, cursor: "pointer" }}>
                <input type="radio" name="condition" checked={filters.condition === c} onChange={() => setFilters(f => ({ ...f, condition: c }))} style={{ accentColor: G }} />
                <span style={{ fontSize: 13 }}>{c === "all" ? "All Conditions" : c}</span>
              </label>
            ))}
          </div>

          <button className="btn btn-green" style={{ width: "100%", justifyContent: "center" }}>Apply Filters</button>
        </div>
      </div>

      {}
      <div style={{ flex: 1 }}>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 20 }}>
          <div>
            <h2 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 22 }}>1,234 Listings</h2>
            <p style={{ fontSize: 13, color: "#6B7280" }}>Showing all available items</p>
          </div>
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <select value={sort} onChange={e => setSort(e.target.value)} style={{ padding: "8px 14px", border: "1.5px solid #E2DDD6", borderRadius: 10, fontSize: 13, outline: "none", background: "#fff" }}>
              <option value="newest">Newest First</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="ending">Ending Soon</option>
              <option value="popular">Most Popular</option>
            </select>
            <div style={{ display: "flex", border: "1.5px solid #E2DDD6", borderRadius: 10, overflow: "hidden" }}>
              {["grid", "list"].map(v => (
                <button key={v} onClick={() => setView(v)} style={{ padding: "8px 12px", background: view === v ? G : "#fff", color: view === v ? "#fff" : "#6B7280", border: "none", fontSize: 16 }}>
                  {v === "grid" ? "⊞" : "☰"}
                </button>
              ))}
            </div>
          </div>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: view === "grid" ? "repeat(3, 1fr)" : "1fr", gap: 18 }}>
          {LISTINGS.concat(LISTINGS).slice(0, 9).map((item, i) => (
            <ListingCard key={`${item.id}-${i}`} item={item} />
          ))}
        </div>

        {}
        <div style={{ display: "flex", alignItems: "center", justifyContent: "center", gap: 8, marginTop: 36 }}>
          {[1, 2, 3, "...", 12].map((p, i) => (
            <button key={i} onClick={() => typeof p === "number" && setPage(p)}
              style={{ width: 36, height: 36, borderRadius: 8, border: `1.5px solid ${page === p ? G : "#E2DDD6"}`, background: page === p ? G : "#fff", color: page === p ? "#fff" : "#6B7280", fontWeight: 600, fontSize: 14 }}>
              {p}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}

function CreateListingPage() {
  const [type, setType] = useState("sale");
  const [step, setStep] = useState(1);
  const [form, setForm] = useState({ title: "", category: "", condition: "New", price: "", description: "" });
  const [images, setImages] = useState([]);

  const types = [
    { id: "sale", ico: "💰", label: "For Sale", desc: "Set a fixed price" },
    { id: "swap", ico: "🔄", label: "Swap", desc: "Trade for another item" },
    { id: "auction", ico: "🔨", label: "Auction", desc: "Let buyers bid" },
  ];

  return (
    <div style={{ maxWidth: 1100, margin: "0 auto", padding: "32px 40px" }}>
      <div style={{ marginBottom: 28 }}>
        <h1 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 28 }}>Post a New Listing</h1>
        <p style={{ color: "#6B7280", fontSize: 14, marginTop: 4 }}>Fill in the details below to list your item</p>
      </div>

      <div style={{ display: "flex", gap: 32 }}>
        {}
        <div style={{ flex: 1 }}>
          {}
          <div className="card" style={{ padding: 24, marginBottom: 20 }}>
            <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Listing Type</h3>
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 12 }}>
              {types.map(t => (
                <div key={t.id} onClick={() => setType(t.id)} style={{ padding: "16px 14px", border: `2px solid ${type === t.id ? G : "#E2DDD6"}`, borderRadius: 14, cursor: "pointer", background: type === t.id ? "#F0FDF4" : "#fff", transition: ".2s", textAlign: "center" }}>
                  <div style={{ fontSize: 28, marginBottom: 8 }}>{t.ico}</div>
                  <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14, marginBottom: 4 }}>{t.label}</div>
                  <div style={{ fontSize: 12, color: "#6B7280" }}>{t.desc}</div>
                  {type === t.id && <div style={{ width: 20, height: 20, borderRadius: "50%", background: G, color: "#fff", fontSize: 11, display: "flex", alignItems: "center", justifyContent: "center", margin: "8px auto 0" }}>✓</div>}
                </div>
              ))}
            </div>
          </div>

          {}
          <div className="card" style={{ padding: 24, marginBottom: 20 }}>
            <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Basic Information</h3>
            <div style={{ marginBottom: 16 }}>
              <label style={{ display: "block", fontSize: 13, fontWeight: 600, marginBottom: 6 }}>Item Title *</label>
              <input value={form.title} onChange={e => setForm(f => ({ ...f, title: e.target.value }))} placeholder="e.g. iPhone 14 Pro 256GB Space Black" className="inp" style={{ paddingLeft: 14 }} />
            </div>
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14, marginBottom: 16 }}>
              <div>
                <label style={{ display: "block", fontSize: 13, fontWeight: 600, marginBottom: 6 }}>Category *</label>
                <select className="inp" style={{ paddingLeft: 14 }}>
                  <option>Electronics</option>
                  <option>Clothing</option>
                  <option>Books</option>
                  <option>Furniture</option>
                  <option>Sports</option>
                </select>
              </div>
              <div>
                <label style={{ display: "block", fontSize: 13, fontWeight: 600, marginBottom: 6 }}>Condition *</label>
                <select className="inp" style={{ paddingLeft: 14 }}>
                  {["New", "Like New", "Good", "Fair", "For Parts"].map(c => <option key={c}>{c}</option>)}
                </select>
              </div>
            </div>
            <div>
              <label style={{ display: "block", fontSize: 13, fontWeight: 600, marginBottom: 6 }}>Description</label>
              <textarea value={form.description} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} placeholder="Describe your item in detail..." rows={4}
                style={{ width: "100%", padding: "11px 14px", border: "1.5px solid #E2DDD6", borderRadius: 11, fontSize: 13.5, outline: "none", resize: "vertical", fontFamily: "'DM Sans', sans-serif" }} />
              <div style={{ fontSize: 11, color: "#9CA3AF", textAlign: "right" }}>{form.description.length}/500</div>
            </div>
          </div>

          {}
          <div className="card" style={{ padding: 24, marginBottom: 20 }}>
            <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Photos</h3>
            <div style={{ border: "2px dashed #D1D5DB", borderRadius: 14, padding: "40px 20px", textAlign: "center", background: "#FAFAFA", cursor: "pointer" }}
              onMouseEnter={e => e.currentTarget.style.borderColor = G} onMouseLeave={e => e.currentTarget.style.borderColor = "#D1D5DB"}>
              <div style={{ fontSize: 36, marginBottom: 10 }}>📸</div>
              <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 600, marginBottom: 4 }}>Drop photos here or click to upload</div>
              <div style={{ fontSize: 12, color: "#9CA3AF" }}>Supports JPG, PNG, WebP — up to 10 images, 5MB each</div>
            </div>
          </div>

          {}
          {type === "sale" && (
            <div className="card" style={{ padding: 24, marginBottom: 20 }}>
              <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Pricing</h3>
              <div className="inp-wrap">
                <span className="inp-ico">💰</span>
                <input value={form.price} onChange={e => setForm(f => ({ ...f, price: e.target.value }))} placeholder="0.00 JD" className="inp" />
              </div>
            </div>
          )}
          {type === "auction" && (
            <div className="card" style={{ padding: 24, marginBottom: 20 }}>
              <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Auction Settings</h3>
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14 }}>
                <div>
                  <label style={{ display: "block", fontSize: 13, fontWeight: 600, marginBottom: 6 }}>Starting Bid (JD)</label>
                  <input placeholder="0.00" className="inp" style={{ paddingLeft: 14 }} />
                </div>
                <div>
                  <label style={{ display: "block", fontSize: 13, fontWeight: 600, marginBottom: 6 }}>End Date & Time</label>
                  <input type="datetime-local" className="inp" style={{ paddingLeft: 14 }} />
                </div>
              </div>
            </div>
          )}
          {type === "swap" && (
            <div className="card" style={{ padding: 24, marginBottom: 20 }}>
              <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Swap Preferences</h3>
              <textarea placeholder="What would you like to swap for? Describe the items you're interested in..." rows={3}
                style={{ width: "100%", padding: "11px 14px", border: "1.5px solid #E2DDD6", borderRadius: 11, fontSize: 13.5, outline: "none", resize: "vertical", fontFamily: "'DM Sans', sans-serif" }} />
            </div>
          )}

          {}
          <div style={{ display: "flex", gap: 12, justifyContent: "flex-end", paddingBottom: 40 }}>
            <button className="btn btn-ghost">Save Draft</button>
            <button className="btn btn-green" style={{ padding: "13px 32px", fontSize: 15 }}>🚀 Publish Listing</button>
          </div>
        </div>

        {}
        <div style={{ width: 280, flexShrink: 0 }}>
          <div style={{ position: "sticky", top: 90 }}>
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14, color: "#6B7280", marginBottom: 12, textTransform: "uppercase", letterSpacing: 1, fontSize: 11 }}>Live Preview</div>
            <ListingCard item={{
              title: form.title || "Your listing title",
              type,
              price: form.price ? `${form.price} JD` : "0 JD",
              condition: form.condition || "New",
              emoji: "📦",
              bg: "#F3F4F6",
              seller: "You",
              rating: "5.0",
              location: "Your City",
            }} />
            <div style={{ marginTop: 14, padding: "12px 16px", background: "#D1FAE5", borderRadius: 12, fontSize: 12, color: "#065F46" }}>
              ✅ Your listing looks great! Make sure to add clear photos for better visibility.
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

const CONVOS = [
  { id: 1, name: "Ahmad Khalil", avatar: "A", msg: "Is the iPhone still available?", time: "2m", unread: 2, online: true },
  { id: 2, name: "Sara Mahmoud", avatar: "S", msg: "Can you lower the price a bit?", time: "15m", unread: 0, online: true },
  { id: 3, name: "Omar Tariq", avatar: "O", msg: "I'd like to swap for my jacket!", time: "1h", unread: 1, online: false },
  { id: 4, name: "Lina Barakat", avatar: "L", msg: "Deal! When can we meet?", time: "3h", unread: 0, online: false },
  { id: 5, name: "Yousef Hani", avatar: "Y", msg: "Thanks for the PS5!", time: "1d", unread: 0, online: false },
];
const CHAT_MSGS = [
  { from: "them", text: "Hi! Is the iPhone 14 Pro still available?", time: "10:30" },
  { from: "me", text: "Yes it is! Still in great condition.", time: "10:32" },
  { from: "them", text: "Great! Would you consider 320 JD?", time: "10:33" },
  { from: "me", text: "I can do 340 JD, final price. It comes with original box and accessories.", time: "10:35" },
  { from: "them", text: "Let me think about it... Can I see more photos?", time: "10:36" },
  { from: "me", text: "Sure! I'll send them now.", time: "10:37" },
  { from: "them", text: "Perfect, I'll take it for 340 JD! Can we meet tomorrow in Amman?", time: "10:45" },
];

function MessagesPage() {
  const [selected, setSelected] = useState(1);
  const [msg, setMsg] = useState("");
  const [messages, setMessages] = useState(CHAT_MSGS);
  const activeConvo = CONVOS.find(c => c.id === selected);

  const send = () => {
    if (!msg.trim()) return;
    setMessages(m => [...m, { from: "me", text: msg, time: new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) }]);
    setMsg("");
  };

  return (
    <div style={{ display: "flex", height: "calc(100vh - 120px)", maxWidth: 1200, margin: "0 auto", padding: "20px 32px", gap: 20 }}>
      {}
      <div className="card" style={{ width: 320, flexShrink: 0, display: "flex", flexDirection: "column", overflow: "hidden" }}>
        <div style={{ padding: "20px 16px 12px" }}>
          <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 18, marginBottom: 12 }}>Messages</h3>
          <div className="inp-wrap">
            <span className="inp-ico" style={{ fontSize: 14 }}>🔍</span>
            <input placeholder="Search conversations..." className="inp" style={{ fontSize: 13 }} />
          </div>
        </div>
        <div style={{ flex: 1, overflowY: "auto" }}>
          {CONVOS.map(c => (
            <div key={c.id} onClick={() => setSelected(c.id)} style={{ display: "flex", alignItems: "center", gap: 12, padding: "12px 16px", cursor: "pointer", background: selected === c.id ? "#F0FDF4" : "transparent", borderLeft: selected === c.id ? `3px solid ${G}` : "3px solid transparent", transition: ".15s" }}>
              <div style={{ position: "relative", flexShrink: 0 }}>
                <div style={{ width: 42, height: 42, borderRadius: "50%", background: G, color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16 }}>{c.avatar}</div>
                {c.online && <div style={{ position: "absolute", bottom: 1, right: 1, width: 10, height: 10, background: "#22C55E", borderRadius: "50%", border: "2px solid #fff" }} />}
              </div>
              <div style={{ flex: 1, overflow: "hidden" }}>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 3 }}>
                  <span style={{ fontWeight: 600, fontSize: 14 }}>{c.name}</span>
                  <span style={{ fontSize: 11, color: "#9CA3AF" }}>{c.time}</span>
                </div>
                <div style={{ fontSize: 12.5, color: "#6B7280", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{c.msg}</div>
              </div>
              {c.unread > 0 && <div style={{ width: 20, height: 20, borderRadius: "50%", background: G, color: "#fff", fontSize: 11, fontWeight: 700, display: "flex", alignItems: "center", justifyContent: "center", flexShrink: 0 }}>{c.unread}</div>}
            </div>
          ))}
        </div>
      </div>

      {}
      <div className="card" style={{ flex: 1, display: "flex", flexDirection: "column", overflow: "hidden" }}>
        {}
        <div style={{ padding: "16px 20px", borderBottom: "1px solid #F3F4F6", display: "flex", alignItems: "center", gap: 12 }}>
          <div style={{ position: "relative" }}>
            <div style={{ width: 40, height: 40, borderRadius: "50%", background: G, color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 15 }}>{activeConvo?.avatar}</div>
            {activeConvo?.online && <div style={{ position: "absolute", bottom: 1, right: 1, width: 10, height: 10, background: "#22C55E", borderRadius: "50%", border: "2px solid #fff" }} />}
          </div>
          <div>
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 15 }}>{activeConvo?.name}</div>
            <div style={{ fontSize: 12, color: activeConvo?.online ? "#22C55E" : "#9CA3AF" }}>{activeConvo?.online ? "● Online" : "Offline"}</div>
          </div>
          <div style={{ marginLeft: "auto", display: "flex", gap: 8 }}>
            <button style={{ padding: "7px 14px", borderRadius: 8, border: "1.5px solid #E2DDD6", background: "#fff", fontSize: 12, fontWeight: 600, color: G }}>View Profile</button>
          </div>
        </div>

        {}
        <div style={{ margin: "12px 20px", padding: "10px 14px", background: "#F8F4EE", borderRadius: 12, display: "flex", alignItems: "center", gap: 12, border: "1px solid #E2DDD6" }}>
          <div style={{ width: 40, height: 40, background: "#e8f0fe", borderRadius: 8, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 20 }}>📱</div>
          <div>
            <div style={{ fontSize: 12, color: "#6B7280" }}>Discussing item</div>
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 600, fontSize: 13 }}>iPhone 14 Pro 256GB</div>
          </div>
          <div style={{ marginLeft: "auto", fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14, color: G }}>340 JD</div>
        </div>

        {}
        <div style={{ flex: 1, overflowY: "auto", padding: "0 20px 12px" }}>
          {messages.map((m, i) => (
            <div key={i} style={{ display: "flex", justifyContent: m.from === "me" ? "flex-end" : "flex-start", marginBottom: 10, animation: "fadeUp .2s ease" }}>
              <div style={{ maxWidth: "65%", padding: "10px 14px", borderRadius: m.from === "me" ? "16px 16px 4px 16px" : "16px 16px 16px 4px", background: m.from === "me" ? G : "#fff", color: m.from === "me" ? "#fff" : DK, fontSize: 14, lineHeight: 1.5, boxShadow: "0 1px 4px rgba(0,0,0,.06)" }}>
                {m.text}
                <div style={{ fontSize: 10, marginTop: 4, opacity: .6, textAlign: m.from === "me" ? "right" : "left" }}>{m.time}</div>
              </div>
            </div>
          ))}
          <div style={{ fontSize: 12, color: "#9CA3AF", textAlign: "center", marginTop: 8 }}>Ahmad is typing...</div>
        </div>

        {}
        <div style={{ padding: "12px 20px", borderTop: "1px solid #F3F4F6", display: "flex", gap: 10, alignItems: "center" }}>
          <button style={{ background: "none", border: "none", fontSize: 20, padding: "4px 8px" }}>😊</button>
          <button style={{ background: "none", border: "none", fontSize: 20, padding: "4px 8px" }}>📎</button>
          <input value={msg} onChange={e => setMsg(e.target.value)} onKeyDown={e => e.key === "Enter" && send()} placeholder="Type a message..." style={{ flex: 1, padding: "10px 16px", border: "1.5px solid #E2DDD6", borderRadius: 99, fontSize: 14, outline: "none" }} />
          <button onClick={send} style={{ width: 40, height: 40, borderRadius: "50%", background: msg.trim() ? G : "#E2DDD6", color: "#fff", border: "none", fontSize: 18, display: "flex", alignItems: "center", justifyContent: "center", transition: ".15s" }}>➤</button>
        </div>
      </div>
    </div>
  );
}

function ProfilePage() {
  const [tab, setTab] = useState("listings");
  const stats = [
    { n: "47", l: "Listings" },
    { n: "23", l: "Swaps" },
    { n: "5", l: "Auctions Won" },
    { n: "94%", l: "Response Rate" },
  ];
  const reviews = [
    { name: "Ahmad K", rating: 5, text: "Great seller! Item exactly as described. Fast response and smooth transaction. Highly recommended!", date: "2 days ago" },
    { name: "Sara M", rating: 5, text: "Amazing swap deal! Very honest and trustworthy. Would definitely trade again.", date: "1 week ago" },
    { name: "Omar T", rating: 4, text: "Good seller, item in great condition. Communication was smooth and meeting was on time.", date: "2 weeks ago" },
  ];
  return (
    <div style={{ maxWidth: 1100, margin: "0 auto", padding: "32px 40px" }}>
      {}
      <div className="card" style={{ marginBottom: 24, overflow: "visible" }}>
        <div style={{ height: 160, background: `linear-gradient(135deg, ${DK} 0%, ${G} 100%)`, borderRadius: "16px 16px 0 0", position: "relative" }}>
          <div style={{ position: "absolute", bottom: -48, left: 32, width: 96, height: 96, borderRadius: "50%", background: G2, border: "4px solid #fff", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 36, fontFamily: "'Syne',sans-serif", fontWeight: 800, color: "#fff" }}>L</div>
        </div>
        <div style={{ padding: "60px 32px 24px" }}>
          <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between" }}>
            <div>
              <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                <h2 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 24 }}>Lina Barakat</h2>
                <span className="pill pill-b" style={{ fontSize: 11 }}>✓ Verified</span>
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 16, marginTop: 6, fontSize: 13.5, color: "#6B7280" }}>
                <span>⭐ 4.9 (124 reviews)</span>
                <span>📍 Amman, Jordan</span>
                <span>📅 Member since Jan 2024</span>
              </div>
              <p style={{ fontSize: 14, color: "#374151", marginTop: 12, maxWidth: 500, lineHeight: 1.6 }}>Passionate about sustainable shopping and fair trades. Electronics enthusiast. Fast responses guaranteed! 🤝</p>
            </div>
            <div style={{ display: "flex", gap: 10 }}>
              <button className="btn btn-outline">💬 Message</button>
              <button className="btn btn-ghost">+ Follow</button>
            </div>
          </div>
          <div style={{ display: "flex", gap: 20, marginTop: 20 }}>
            {stats.map(s => (
              <div key={s.l} style={{ textAlign: "center", padding: "12px 20px", background: "#F8F4EE", borderRadius: 12 }}>
                <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 20, color: G }}>{s.n}</div>
                <div style={{ fontSize: 12, color: "#6B7280" }}>{s.l}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {}
      <div style={{ display: "flex", gap: 6, marginBottom: 20 }}>
        {["listings", "reviews", "swaps"].map(t => (
          <button key={t} onClick={() => setTab(t)} style={{ padding: "10px 24px", borderRadius: 10, border: `1.5px solid ${tab === t ? G : "#E2DDD6"}`, background: tab === t ? G : "#fff", color: tab === t ? "#fff" : "#6B7280", fontFamily: "'Syne',sans-serif", fontWeight: 600, fontSize: 14, transition: ".15s" }}>
            {t.charAt(0).toUpperCase() + t.slice(1)}
          </button>
        ))}
      </div>

      {tab === "listings" && (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 18 }}>
          {LISTINGS.map(item => <ListingCard key={item.id} item={item} />)}
        </div>
      )}

      {tab === "reviews" && (
        <div>
          <div className="card" style={{ padding: 24, marginBottom: 20, display: "flex", gap: 40 }}>
            <div style={{ textAlign: "center" }}>
              <div style={{ fontFamily: "'Syne',sans-serif", fontSize: 56, fontWeight: 800, color: G, lineHeight: 1 }}>4.9</div>
              <div style={{ fontSize: 24, marginTop: 4 }}>⭐⭐⭐⭐⭐</div>
              <div style={{ fontSize: 13, color: "#6B7280", marginTop: 4 }}>124 reviews</div>
            </div>
            <div style={{ flex: 1 }}>
              {[5, 4, 3, 2, 1].map(n => (
                <div key={n} style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 8 }}>
                  <span style={{ fontSize: 12, width: 16 }}>{n}★</span>
                  <div style={{ flex: 1, height: 8, background: "#F3F4F6", borderRadius: 99, overflow: "hidden" }}>
                    <div style={{ height: "100%", background: G, borderRadius: 99, width: `${n === 5 ? 85 : n === 4 ? 12 : 2}%` }} />
                  </div>
                  <span style={{ fontSize: 12, color: "#6B7280", width: 30 }}>{n === 5 ? 105 : n === 4 ? 15 : 4}</span>
                </div>
              ))}
            </div>
          </div>
          {reviews.map((r, i) => (
            <div key={i} className="card" style={{ padding: 20, marginBottom: 14 }}>
              <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 12 }}>
                <div style={{ width: 40, height: 40, borderRadius: "50%", background: G, color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "'Syne',sans-serif", fontWeight: 700 }}>{r.name[0]}</div>
                <div>
                  <div style={{ fontWeight: 600, fontSize: 14 }}>{r.name}</div>
                  <div style={{ fontSize: 12, color: "#9CA3AF" }}>{r.date}</div>
                </div>
                <div style={{ marginLeft: "auto", fontSize: 16 }}>{"⭐".repeat(r.rating)}</div>
              </div>
              <p style={{ fontSize: 14, color: "#374151", lineHeight: 1.65 }}>{r.text}</p>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

function AuctionPage() {
  const [bid, setBid] = useState("");
  const [currentBid, setCurrentBid] = useState(185);
  const [bidPlaced, setBidPlaced] = useState(false);
  const [timeLeft, setTimeLeft] = useState({ h: 2, m: 14, s: 33 });

  useEffect(() => {
    const t = setInterval(() => {
      setTimeLeft(tl => {
        let { h, m, s } = tl;
        if (s > 0) return { h, m, s: s - 1 };
        if (m > 0) return { h, m: m - 1, s: 59 };
        if (h > 0) return { h: h - 1, m: 59, s: 59 };
        return tl;
      });
    }, 1000);
    return () => clearInterval(t);
  }, []);

  const placeBid = () => {
    const v = parseFloat(bid);
    if (v > currentBid) {
      setCurrentBid(v);
      setBidPlaced(true);
      setBid("");
      setTimeout(() => setBidPlaced(false), 3000);
    }
  };

  const bids = [
    { user: "Bidder_***7", amount: currentBid, time: "Just now", you: true },
    { user: "Bidder_***2", amount: 175, time: "5m ago" },
    { user: "Bidder_***9", amount: 160, time: "12m ago" },
    { user: "Bidder_***4", amount: 145, time: "28m ago" },
    { user: "Bidder_***1", amount: 130, time: "1h ago" },
  ];

  return (
    <div style={{ maxWidth: 1100, margin: "0 auto", padding: "32px 40px" }}>
      <div style={{ fontSize: 13, color: "#6B7280", marginBottom: 16 }}>🏠 Home › Electronics › <strong>Sony WH-1000XM5</strong></div>
      <div style={{ display: "flex", gap: 36 }}>
        {}
        <div style={{ flex: "0 0 55%" }}>
          <div style={{ height: 380, background: "#fce8e8", borderRadius: 20, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 100, marginBottom: 14, border: "2px solid #F3F4F6" }}>🎧</div>
          <div style={{ display: "flex", gap: 10 }}>
            {["🎧", "📦", "🔌", "📝"].map((e, i) => (
              <div key={i} style={{ width: 72, height: 72, background: "#fce8e8", borderRadius: 12, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 28, cursor: "pointer", border: i === 0 ? `2px solid ${G}` : "2px solid transparent" }}>{e}</div>
            ))}
          </div>
        </div>

        {}
        <div style={{ flex: 1 }}>
          <span className="pill" style={{ background: "#FEE2E2", color: "#DC2626", fontSize: 13, fontWeight: 700, marginBottom: 12, display: "inline-flex", animation: "pulse 2s infinite" }}>🔴 LIVE AUCTION</span>
          <h1 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 26, marginBottom: 8, marginTop: 8 }}>Sony WH-1000XM5 Wireless Headphones</h1>
          <div style={{ display: "flex", gap: 10, marginBottom: 20 }}>
            <span className="pill pill-g">Good Condition</span>
            <span style={{ fontSize: 13, color: "#9CA3AF" }}>👁 234 watching · 📍 Irbid</span>
          </div>

          {}
          <div style={{ background: DK, borderRadius: 16, padding: "16px 20px", marginBottom: 20 }}>
            <div style={{ fontSize: 12, color: "rgba(255,255,255,.5)", marginBottom: 10, textTransform: "uppercase", letterSpacing: 1 }}>Auction Ends In</div>
            <div style={{ display: "flex", gap: 14 }}>
              {[["00", "Days"], [String(timeLeft.h).padStart(2, "0"), "Hours"], [String(timeLeft.m).padStart(2, "0"), "Mins"], [String(timeLeft.s).padStart(2, "0"), "Secs"]].map(([n, l]) => (
                <div key={l} style={{ textAlign: "center" }}>
                  <div style={{ fontFamily: "'Syne',sans-serif", fontSize: 32, fontWeight: 800, color: "#B7E4C7", lineHeight: 1 }}>{n}</div>
                  <div style={{ fontSize: 11, color: "rgba(255,255,255,.4)", marginTop: 4 }}>{l}</div>
                </div>
              ))}
            </div>
          </div>

          {}
          <div style={{ marginBottom: 20 }}>
            <div style={{ fontSize: 13, color: "#6B7280", marginBottom: 4 }}>Current Bid</div>
            <div style={{ fontFamily: "'Syne',sans-serif", fontSize: 38, fontWeight: 800, color: G }}>{currentBid} JD</div>
            <div style={{ display: "inline-flex", alignItems: "center", gap: 6, padding: "5px 14px", background: bidPlaced ? "#D1FAE5" : "#DBEAFE", color: bidPlaced ? "#065F46" : "#1E40AF", borderRadius: 99, fontSize: 13, fontWeight: 600, marginTop: 6 }}>
              {bidPlaced ? "✅ You're currently winning!" : "💙 You've been outbid"}
            </div>
          </div>

          {}
          <div style={{ marginBottom: 20 }}>
            <div style={{ fontSize: 13, color: "#6B7280", marginBottom: 8 }}>Min next bid: <strong>{currentBid + 5} JD</strong></div>
            <div style={{ display: "flex", gap: 10, marginBottom: 10 }}>
              {[10, 25, 50].map(inc => (
                <button key={inc} onClick={() => setBid(String(currentBid + inc))}
                  style={{ flex: 1, padding: "8px", border: "1.5px solid #E2DDD6", borderRadius: 10, background: "#fff", fontSize: 13, fontWeight: 600, color: G }}>
                  +{inc} JD
                </button>
              ))}
            </div>
            <div style={{ display: "flex", gap: 10 }}>
              <input value={bid} onChange={e => setBid(e.target.value)} placeholder={`${currentBid + 5} or more`}
                style={{ flex: 1, padding: "12px 16px", border: "1.5px solid #E2DDD6", borderRadius: 12, fontSize: 15, fontFamily: "'Syne',sans-serif", fontWeight: 700, outline: "none" }} />
              <button onClick={placeBid} className="btn btn-green" style={{ padding: "12px 24px", fontSize: 15 }}>Place Bid 🔨</button>
            </div>
          </div>

          {}
          <div style={{ padding: "14px 16px", background: "#F8F4EE", borderRadius: 14, marginBottom: 16 }}>
            <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
              <div style={{ width: 40, height: 40, borderRadius: "50%", background: G, color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "'Syne',sans-serif", fontWeight: 700 }}>S</div>
              <div>
                <div style={{ fontWeight: 600, fontSize: 14 }}>Sara Mahmoud</div>
                <div style={{ fontSize: 12, color: "#9CA3AF" }}>⭐ 4.7 · 34 listings · Irbid</div>
              </div>
              <button className="btn btn-outline btn-sm" style={{ marginLeft: "auto" }}>Message</button>
            </div>
          </div>
        </div>
      </div>

      {}
      <div className="card" style={{ marginTop: 28, padding: 24 }}>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 16 }}>
          <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 18 }}>Bid History ({bids.length} bids)</h3>
        </div>
        {bids.map((b, i) => (
          <div key={i} style={{ display: "flex", alignItems: "center", gap: 14, padding: "12px 16px", background: i === 0 ? "#F0FDF4" : i % 2 === 0 ? "#FAFAFA" : "#fff", borderRadius: 10, marginBottom: 6, border: i === 0 ? `1px solid ${G}` : "1px solid transparent" }}>
            <div style={{ width: 36, height: 36, borderRadius: "50%", background: i === 0 ? G : "#E5E7EB", color: i === 0 ? "#fff" : "#6B7280", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 14, fontWeight: 700 }}>{b.user[0]}</div>
            <div style={{ flex: 1 }}>
              <div style={{ fontWeight: 600, fontSize: 14 }}>{b.user} {b.you && <span className="pill pill-g" style={{ fontSize: 10 }}>You</span>}</div>
            </div>
            {i === 0 && <span className="pill pill-g" style={{ fontSize: 11 }}>Highest Bid</span>}
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 16, color: G }}>{b.amount} JD</div>
            <div style={{ fontSize: 12, color: "#9CA3AF", width: 70, textAlign: "right" }}>{b.time}</div>
          </div>
        ))}
      </div>
    </div>
  );
}

function AdminPage({ onNav }) {
  const kpis = [
    { label: "Total Users", value: "12,847", delta: "+8.2%", ico: "👥", color: "#EEF2FF" },
    { label: "Active Listings", value: "1,234", delta: "+3.5%", ico: "📦", color: "#F0FDF4" },
    { label: "Revenue Today", value: "2,450 JD", delta: "+12.1%", ico: "💰", color: "#FFFBEB" },
    { label: "Open Reports", value: "17", delta: "-5", ico: "🚨", color: "#FFF1F2" },
  ];
  const recentUsers = [
    { name: "Ahmad Khalil", email: "ahmad@gmail.com", role: "User", status: "active", joined: "Today" },
    { name: "Sara Mahmoud", email: "sara@email.com", role: "User", status: "active", joined: "Yesterday" },
    { name: "Omar Tariq", email: "omar@email.com", role: "Seller", status: "suspended", joined: "3d ago" },
    { name: "Lina Barakat", email: "lina@email.com", role: "Seller", status: "active", joined: "1w ago" },
    { name: "Yousef Hani", email: "yousef@email.com", role: "User", status: "active", joined: "2w ago" },
  ];

  return (
    <div style={{ maxWidth: 1200, margin: "0 auto", padding: "28px 32px" }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 28 }}>
        <div>
          <h1 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 26 }}>Admin Dashboard</h1>
          <p style={{ fontSize: 14, color: "#6B7280", marginTop: 4 }}>Welcome back, Admin. Here's what's happening today.</p>
        </div>
        <div style={{ display: "flex", gap: 10 }}>
          <button className="btn btn-ghost btn-sm">📊 Export Report</button>
          <button className="btn btn-green btn-sm">+ Add User</button>
        </div>
      </div>

      {}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 16, marginBottom: 28 }}>
        {kpis.map(k => (
          <div key={k.label} className="card" style={{ padding: 20 }}>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 12 }}>
              <div style={{ width: 44, height: 44, borderRadius: 12, background: k.color, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 22 }}>{k.ico}</div>
              <span style={{ fontSize: 12, fontWeight: 600, color: k.delta.startsWith("+") ? "#065F46" : "#DC2626", background: k.delta.startsWith("+") ? "#D1FAE5" : "#FEE2E2", padding: "3px 9px", borderRadius: 99 }}>{k.delta}</span>
            </div>
            <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 24, marginBottom: 4 }}>{k.value}</div>
            <div style={{ fontSize: 13, color: "#6B7280" }}>{k.label}</div>
          </div>
        ))}
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 20, marginBottom: 24 }}>
        {}
        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Activity Overview</h3>
          <div style={{ height: 160, background: "#F8F4EE", borderRadius: 12, display: "flex", alignItems: "flex-end", padding: "16px 16px 0", gap: 8 }}>
            {[40, 65, 45, 80, 55, 90, 70, 85, 60, 95, 75, 100].map((h, i) => (
              <div key={i} style={{ flex: 1, height: `${h}%`, background: i === 11 ? G : `rgba(44,110,73,${.3 + i * .05})`, borderRadius: "6px 6px 0 0", transition: ".3s" }} />
            ))}
          </div>
          <div style={{ display: "flex", justifyContent: "space-between", marginTop: 8, fontSize: 11, color: "#9CA3AF" }}>
            {["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"].map(m => <span key={m}>{m}</span>)}
          </div>
        </div>

        {}
        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16, marginBottom: 16 }}>Recent Reports</h3>
          {[
            { type: "Fraud", user: "user_4821", status: "open", time: "1h ago" },
            { type: "Fake Item", user: "user_2234", status: "reviewing", time: "3h ago" },
            { type: "Spam", user: "user_9903", status: "resolved", time: "1d ago" },
            { type: "Harassment", user: "user_1117", status: "open", time: "2d ago" },
          ].map((r, i) => (
            <div key={i} style={{ display: "flex", alignItems: "center", gap: 12, padding: "10px 0", borderBottom: i < 3 ? "1px solid #F3F4F6" : "" }}>
              <div style={{ width: 8, height: 8, borderRadius: "50%", background: r.status === "open" ? "#EF4444" : r.status === "reviewing" ? O : "#22C55E", flexShrink: 0 }} />
              <div style={{ flex: 1 }}>
                <div style={{ fontWeight: 600, fontSize: 14 }}>{r.type}</div>
                <div style={{ fontSize: 12, color: "#9CA3AF" }}>{r.user} · {r.time}</div>
              </div>
              <span className={`pill ${r.status === "open" ? "pill-o" : r.status === "reviewing" ? "pill-y" : "pill-g"}`} style={{ fontSize: 11 }}>{r.status}</span>
            </div>
          ))}
        </div>
      </div>

      {}
      <div className="card" style={{ padding: 24 }}>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 16 }}>
          <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 16 }}>Recent Users</h3>
          <button onClick={() => onNav("admin-users")} style={{ color: G, background: "none", border: "none", fontWeight: 600, fontSize: 13 }}>View All →</button>
        </div>
        <table style={{ width: "100%", borderCollapse: "collapse" }}>
          <thead>
            <tr style={{ borderBottom: "2px solid #F3F4F6" }}>
              {["User", "Email", "Role", "Status", "Joined", "Actions"].map(h => (
                <th key={h} style={{ textAlign: "left", padding: "8px 12px", fontSize: 12, fontWeight: 600, color: "#9CA3AF", textTransform: "uppercase", letterSpacing: .5 }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {recentUsers.map((u, i) => (
              <tr key={i} style={{ borderBottom: "1px solid #F9FAFB" }}>
                <td style={{ padding: "12px 12px" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                    <div style={{ width: 32, height: 32, borderRadius: "50%", background: G, color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontWeight: 700, fontSize: 13 }}>{u.name[0]}</div>
                    <span style={{ fontWeight: 600, fontSize: 14 }}>{u.name}</span>
                  </div>
                </td>
                <td style={{ padding: "12px", fontSize: 13, color: "#6B7280" }}>{u.email}</td>
                <td style={{ padding: "12px" }}><span className="pill pill-b" style={{ fontSize: 11 }}>{u.role}</span></td>
                <td style={{ padding: "12px" }}><span className={`pill ${u.status === "active" ? "pill-g" : "pill-o"}`} style={{ fontSize: 11 }}>{u.status}</span></td>
                <td style={{ padding: "12px", fontSize: 13, color: "#9CA3AF" }}>{u.joined}</td>
                <td style={{ padding: "12px" }}>
                  <div style={{ display: "flex", gap: 6 }}>
                    <button style={{ padding: "4px 10px", borderRadius: 6, border: "1.5px solid #E2DDD6", background: "#fff", fontSize: 12, color: "#374151" }}>Edit</button>
                    <button style={{ padding: "4px 10px", borderRadius: 6, border: "1.5px solid #FCA5A5", background: "#FFF1F2", fontSize: 12, color: "#DC2626" }}>{u.status === "active" ? "Suspend" : "Restore"}</button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function LoginPage({ onNav }) {
  const [email, setEmail] = useState("");
  const [pass, setPass] = useState("");
  const [showPass, setShowPass] = useState(false);
  return (
    <div style={{ minHeight: "100vh", display: "flex", background: CR }}>
      {}
      <div style={{ flex: 1, display: "flex", alignItems: "center", justifyContent: "center", padding: 48 }}>
        <div style={{ width: "100%", maxWidth: 420, animation: "fadeUp .5s ease" }}>
          <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 22, color: DK, display: "flex", alignItems: "center", gap: 10, marginBottom: 32 }}>
            <div style={{ width: 32, height: 32, background: G, borderRadius: 8, display: "flex", alignItems: "center", justifyContent: "center" }}>🔄</div>
            SwapIt
          </div>
          <h2 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 28, marginBottom: 6 }}>Welcome back 👋</h2>
          <p style={{ color: "#6B7280", fontSize: 15, marginBottom: 28 }}>Sign in to your account to continue</p>

          <div className="inp-wrap" style={{ marginBottom: 14 }}>
            <span className="inp-ico">✉️</span>
            <input type="email" value={email} onChange={e => setEmail(e.target.value)} placeholder="Email address" className="inp" />
          </div>
          <div className="inp-wrap" style={{ marginBottom: 8 }}>
            <span className="inp-ico">🔒</span>
            <input type={showPass ? "text" : "password"} value={pass} onChange={e => setPass(e.target.value)} placeholder="Password" className="inp" style={{ paddingRight: 44 }} />
            <button onClick={() => setShowPass(!showPass)} style={{ position: "absolute", right: 14, top: "50%", transform: "translateY(-50%)", background: "none", border: "none", fontSize: 16, cursor: "pointer" }}>{showPass ? "🙈" : "👁"}</button>
          </div>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 22 }}>
            <label style={{ display: "flex", alignItems: "center", gap: 8, fontSize: 13 }}>
              <input type="checkbox" style={{ accentColor: G }} /> Remember me
            </label>
            <button style={{ color: G, background: "none", border: "none", fontSize: 13, fontWeight: 600 }}>Forgot password?</button>
          </div>

          <button onClick={() => onNav("home")} className="btn btn-green" style={{ width: "100%", justifyContent: "center", padding: "13px", fontSize: 15 }}>Sign In →</button>

          <div style={{ display: "flex", alignItems: "center", gap: 14, margin: "20px 0" }}>
            <div style={{ flex: 1, height: 1, background: "#E2DDD6" }} />
            <span style={{ fontSize: 12, color: "#9CA3AF" }}>or sign in with</span>
            <div style={{ flex: 1, height: 1, background: "#E2DDD6" }} />
          </div>
          <div style={{ display: "flex", gap: 10 }}>
            {[["Google", "🔵"], ["Facebook", "🟦"]].map(([n, e]) => (
              <button key={n} style={{ flex: 1, padding: "11px", border: "1.5px solid #E2DDD6", borderRadius: 12, background: "#fff", fontSize: 14, fontWeight: 600, display: "flex", alignItems: "center", justifyContent: "center", gap: 8 }}>{e} {n}</button>
            ))}
          </div>
          <p style={{ textAlign: "center", marginTop: 22, fontSize: 13.5, color: "#6B7280" }}>
            Don't have an account? <button onClick={() => onNav("register")} style={{ color: G, background: "none", border: "none", fontWeight: 700, cursor: "pointer" }}>Sign up free →</button>
          </p>
        </div>
      </div>
      {}
      <div style={{ flex: "0 0 44%", background: DK, display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", padding: 56, position: "relative", overflow: "hidden" }}>
        <div style={{ position: "absolute", width: 500, height: 500, borderRadius: "50%", background: "radial-gradient(circle,rgba(44,110,73,.5),transparent 65%)", top: -150, right: -150, pointerEvents: "none" }} />
        <div style={{ position: "relative", zIndex: 2, textAlign: "center" }}>
          <h2 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 34, color: "#fff", marginBottom: 10 }}>Your marketplace, <span style={{ color: O }}>your rules.</span></h2>
          <p style={{ color: "rgba(255,255,255,.5)", fontSize: 15, marginBottom: 40 }}>Join thousands trading smarter every day</p>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14 }}>
            {[
              { n: "12K+", l: "Active Listings", c: "#B7E4C7" },
              { n: "4.8★", l: "Avg Rating", c: O },
              { n: "850+", l: "Swaps Done", c: "#93C5FD" },
              { n: "200+", l: "Live Auctions", c: "#FCD34D" },
            ].map(s => (
              <div key={s.l} style={{ background: "rgba(255,255,255,.07)", border: "1px solid rgba(255,255,255,.1)", borderRadius: 14, padding: "18px 16px", textAlign: "center" }}>
                <div style={{ fontFamily: "'Syne',sans-serif", fontSize: 26, fontWeight: 800, color: s.c }}>{s.n}</div>
                <div style={{ fontSize: 12, color: "rgba(255,255,255,.45)", marginTop: 4 }}>{s.l}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

function SwapsPage() {
  const [tab, setTab] = useState("received");
  const offers = [
    { from: "Ahmad K", avatar: "A", theirItem: { name: "PlayStation 5", emoji: "🎮", value: "420 JD" }, myItem: { name: "iPhone 14 Pro", emoji: "📱", value: "350 JD" }, msg: "I think PS5 is worth the difference! Happy to add 50 JD cash.", time: "2h ago", status: "pending" },
    { from: "Sara M", avatar: "S", theirItem: { name: "Canon EOS R50", emoji: "📷", value: "480 JD" }, myItem: { name: "MacBook Air M2", emoji: "💻", value: "720 JD" }, msg: "Interested in a swap? My camera is brand new.", time: "1d ago", status: "accepted" },
    { from: "Omar T", avatar: "O", theirItem: { name: "Trek Road Bike", emoji: "🚴", value: "380 JD" }, myItem: { name: "Sony Headphones", emoji: "🎧", value: "120 JD" }, msg: "Would you be open to swapping?", time: "3d ago", status: "rejected" },
  ];
  const statusColors = { pending: ["pill-y", "Pending"], accepted: ["pill-g", "Accepted ✓"], rejected: ["pill-o", "Rejected"] };

  return (
    <div style={{ maxWidth: 900, margin: "0 auto", padding: "32px 40px" }}>
      <h1 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 26, marginBottom: 6 }}>Swap Offers</h1>
      <p style={{ fontSize: 14, color: "#6B7280", marginBottom: 24 }}>Manage all your incoming and outgoing swap proposals</p>

      <div style={{ display: "flex", gap: 6, marginBottom: 24 }}>
        {["received", "sent"].map(t => (
          <button key={t} onClick={() => setTab(t)} style={{ padding: "10px 24px", borderRadius: 10, border: `1.5px solid ${tab === t ? G : "#E2DDD6"}`, background: tab === t ? G : "#fff", color: tab === t ? "#fff" : "#6B7280", fontFamily: "'Syne',sans-serif", fontWeight: 600, fontSize: 14 }}>
            {t === "received" ? "📥 Received" : "📤 Sent"} <span style={{ background: tab === t ? "rgba(255,255,255,.2)" : "#E2DDD6", borderRadius: 99, padding: "1px 8px", fontSize: 12, marginLeft: 4 }}>{offers.length}</span>
          </button>
        ))}
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
        {offers.map((o, i) => {
          const [sc, sl] = statusColors[o.status];
          return (
            <div key={i} className="card" style={{ padding: 22, border: o.status === "accepted" ? `1.5px solid ${G}` : "1px solid #E2DDD6" }}>
              <div style={{ display: "flex", alignItems: "center", gap: 20 }}>
                {}
                <div style={{ flex: 1, padding: "14px", background: "#F8F4EE", borderRadius: 14, display: "flex", gap: 12, alignItems: "center" }}>
                  <div style={{ width: 52, height: 52, background: "#fff", borderRadius: 12, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 26 }}>{o.theirItem.emoji}</div>
                  <div>
                    <div style={{ fontSize: 11, color: "#9CA3AF", marginBottom: 2 }}>Their offer</div>
                    <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14 }}>{o.theirItem.name}</div>
                    <div style={{ fontSize: 13, color: G, fontWeight: 600 }}>{o.theirItem.value}</div>
                  </div>
                </div>

                {}
                <div style={{ fontSize: 24, flexShrink: 0, color: "#9CA3AF" }}>⇄</div>

                {}
                <div style={{ flex: 1, padding: "14px", background: "#EDF7ED", borderRadius: 14, display: "flex", gap: 12, alignItems: "center" }}>
                  <div style={{ width: 52, height: 52, background: "#fff", borderRadius: 12, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 26 }}>{o.myItem.emoji}</div>
                  <div>
                    <div style={{ fontSize: 11, color: "#9CA3AF", marginBottom: 2 }}>Your item</div>
                    <div style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 14 }}>{o.myItem.name}</div>
                    <div style={{ fontSize: 13, color: G, fontWeight: 600 }}>{o.myItem.value}</div>
                  </div>
                </div>
              </div>

              <div style={{ display: "flex", alignItems: "center", gap: 12, marginTop: 16 }}>
                <div style={{ width: 32, height: 32, borderRadius: "50%", background: G, color: "#fff", display: "flex", alignItems: "center", justifyContent: "center", fontWeight: 700, fontSize: 13 }}>{o.avatar}</div>
                <div style={{ flex: 1 }}>
                  <span style={{ fontWeight: 600, fontSize: 14 }}>{o.from}</span>
                  <span style={{ fontSize: 13, color: "#6B7280", marginLeft: 8 }}>"{o.msg}"</span>
                </div>
                <span style={{ fontSize: 12, color: "#9CA3AF" }}>{o.time}</span>
                <span className={`pill ${sc}`}>{sl}</span>
              </div>

              {o.status === "pending" && (
                <div style={{ display: "flex", gap: 10, marginTop: 14 }}>
                  <button className="btn btn-green btn-sm">✓ Accept</button>
                  <button className="btn btn-sm" style={{ border: "1.5px solid #FCA5A5", background: "#FFF1F2", color: "#DC2626" }}>✗ Reject</button>
                  <button className="btn btn-sm" style={{ border: `1.5px solid ${O}`, background: "#FEF3E8", color: O }}>↩ Counter Offer</button>
                </div>
              )}
              {o.status === "accepted" && (
                <div style={{ marginTop: 14, padding: "10px 14px", background: "#F0FDF4", borderRadius: 10, display: "flex", alignItems: "center", gap: 10 }}>
                  <span style={{ color: G, fontSize: 16 }}>✅</span>
                  <span style={{ fontSize: 13, color: "#065F46", fontWeight: 600 }}>Swap Accepted — Arrange your exchange!</span>
                  <button className="btn btn-green btn-sm" style={{ marginLeft: "auto" }}>💬 Contact</button>
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}

function WishlistPage() {
  const [items, setItems] = useState(LISTINGS.slice(0, 6).map((l, i) => ({ ...l, priceDrop: i === 1 || i === 4 })));
  const remove = id => setItems(prev => prev.filter(i => i.id !== id));
  return (
    <div style={{ maxWidth: 1100, margin: "0 auto", padding: "32px 40px" }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 28 }}>
        <div>
          <h1 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 26 }}>My Wishlist ❤️</h1>
          <p style={{ fontSize: 14, color: "#6B7280", marginTop: 4 }}>{items.length} saved items</p>
        </div>
        <select style={{ padding: "8px 14px", border: "1.5px solid #E2DDD6", borderRadius: 10, fontSize: 13, outline: "none" }}>
          <option>Date Saved</option>
          <option>Price: Low to High</option>
          <option>Ending Soon</option>
        </select>
      </div>
      {items.length === 0 ? (
        <div style={{ textAlign: "center", padding: "80px 20px" }}>
          <div style={{ fontSize: 72, marginBottom: 16 }}>💔</div>
          <h3 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 20, marginBottom: 8 }}>Nothing saved yet</h3>
          <p style={{ color: "#6B7280", marginBottom: 20 }}>Browse listings and save items you love!</p>
          <button className="btn btn-green">Browse Listings</button>
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 20 }}>
          {items.map(item => (
            <div key={item.id} style={{ position: "relative" }}>
              {item.priceDrop && <div style={{ position: "absolute", top: -8, left: 12, zIndex: 10, background: G, color: "#fff", borderRadius: 99, padding: "3px 12px", fontSize: 11, fontWeight: 700 }}>📉 Price Dropped!</div>}
              <ListingCard item={{ ...item, saved: true }} />
              <button onClick={() => remove(item.id)} style={{ position: "absolute", top: 46, right: 10, width: 28, height: 28, borderRadius: "50%", background: "#FFF1F2", border: "1px solid #FCA5A5", color: "#DC2626", fontSize: 13, display: "flex", alignItems: "center", justifyContent: "center" }}>×</button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

function NotFoundPage({ onNav }) {
  return (
    <div style={{ minHeight: "70vh", display: "flex", alignItems: "center", justifyContent: "center", textAlign: "center", padding: 40 }}>
      <div style={{ animation: "fadeUp .5s ease" }}>
        <div style={{ fontSize: 90, marginBottom: 16 }}>🔄</div>
        <h1 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 800, fontSize: 52, color: DK, marginBottom: 8 }}>404</h1>
        <h2 style={{ fontFamily: "'Syne',sans-serif", fontWeight: 700, fontSize: 24, marginBottom: 12 }}>Page not found</h2>
        <p style={{ color: "#6B7280", fontSize: 16, marginBottom: 32, maxWidth: 360 }}>Looks like this swap went somewhere else. Let's get you back on track.</p>
        <button onClick={() => onNav("home")} className="btn btn-green" style={{ padding: "14px 32px", fontSize: 15 }}>🏠 Back to Home</button>
      </div>
    </div>
  );
}

export default function App() {
  const [page, setPage] = useState("login");
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const showNav = !["login", "register"].includes(page);

  const renderPage = () => {
    switch (page) {
      case "login": return <LoginPage onNav={setPage} />;
      case "home": return <HomePage onNav={setPage} />;
      case "browse": return <BrowsePage />;
      case "create": return <CreateListingPage />;
      case "messages": return <MessagesPage />;
      case "profile": return <ProfilePage />;
      case "auctions": return <AuctionPage />;
      case "swaps": return <SwapsPage />;
      case "wishlist": return <WishlistPage />;
      case "admin": return <AdminPage onNav={setPage} />;
      default: return <NotFoundPage onNav={setPage} />;
    }
  };

  return (
    <>
      <style>{css}</style>
      {page === "login" ? (
        <LoginPage onNav={setPage} />
      ) : (
        <div style={{ display: "flex", minHeight: "100vh" }}>
          {sidebarOpen && <SideNav onNav={setPage} active={page} />}
          <div style={{ flex: 1, overflow: "auto" }}>
            <Navbar onNav={setPage} active={page} />
            <div style={{ animation: "fadeUp .3s ease" }}>
              {renderPage()}
            </div>
          </div>
          {}
          <button onClick={() => setSidebarOpen(!sidebarOpen)}
            style={{ position: "fixed", bottom: 28, left: sidebarOpen ? 236 : 16, zIndex: 300, width: 36, height: 36, borderRadius: "50%", background: DK, color: "#fff", border: "none", fontSize: 14, transition: ".25s", boxShadow: "0 4px 16px rgba(0,0,0,.2)" }}>
            {sidebarOpen ? "◀" : "▶"}
          </button>
        </div>
      )}
    </>
  );
}
